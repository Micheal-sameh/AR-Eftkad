<?php

namespace App\Repository;

use App\Enums\BoolType;
use App\Enums\UserRole;
use App\Models\Eftkad;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class EftkadRepository
{
    public function __construct(protected Eftkad $model) {}

    public const DEFAULT_PER_PAGE = 10;

    public const MAX_PER_PAGE = 50;

    /**
     * @param  array{father?: ?string, servant?: ?string, type?: ?string, date?: ?string, date_from?: ?string, date_to?: ?string}  $filters
     */
    public function index(array $filters = [], int $perPage = self::DEFAULT_PER_PAGE, ?User $viewer = null): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));

        return $this->visibleTo($viewer)
            ->when($filters['father'] ?? null, fn (Builder $q, $v) => $q->where('father_membership_code', $v))
            ->when($filters['servant'] ?? null, fn (Builder $q, $v) => $q->where('servant_membership_code', $v))
            ->when($filters['type'] ?? null, fn (Builder $q, $v) => $q->where('type', (int) $v))
            ->when($filters['date'] ?? null, fn (Builder $q, $v) => $this->applyDateFilter($q, trim($v)))
            ->when($this->parseDay($filters['date_from'] ?? null), fn (Builder $q, Carbon $d) => $q->where('date', '>=', $d->startOfDay()))
            ->when($this->parseDay($filters['date_to'] ?? null), fn (Builder $q, Carbon $d) => $q->where('date', '<=', $d->endOfDay()))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Admins, superadmins and owners see every visit; everyone else only the visits they are
     * the assigned father or servant of.
     */
    protected function visibleTo(?User $viewer): Builder
    {
        $query = $this->model->query();

        if ($viewer && $viewer->hasAnyRole(UserRole::MANAGERS)) {
            return $query;
        }

        $code = $viewer?->membership_code;

        return $query->where(fn (Builder $q) => $code
            ? $q->where('father_membership_code', $code)->orWhere('servant_membership_code', $code)
            : $q->whereRaw('1 = 0'));
    }

    /** d-m-Y (the format the API speaks) to a Carbon day, or null when empty/invalid. */
    protected function parseDay(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            $day = Carbon::createFromFormat('!d-m-Y', $value);
        } catch (\Carbon\Exceptions\InvalidFormatException) {
            return null;
        }

        return $day && $day->format('d-m-Y') === $value ? $day : null;
    }

    /**
     * Complete day (12-10-2023), month (10-2023) or year (2023) become index-friendly
     * range scans; anything else falls back to a substring match on the d-m-Y text.
     */
    protected function applyDateFilter(Builder $query, string $value): void
    {
        $range = null;

        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $value, $m) && checkdate((int) $m[2], (int) $m[1], (int) $m[3])) {
            $start = Carbon::create((int) $m[3], (int) $m[2], (int) $m[1])->startOfDay();
            $range = [$start, $start->copy()->endOfDay()];
        } elseif (preg_match('/^(\d{1,2})-(\d{4})$/', $value, $m) && (int) $m[1] >= 1 && (int) $m[1] <= 12) {
            $start = Carbon::create((int) $m[2], (int) $m[1], 1)->startOfDay();
            $range = [$start, $start->copy()->endOfMonth()];
        } elseif (preg_match('/^\d{4}$/', $value)) {
            $start = Carbon::create((int) $value, 1, 1)->startOfDay();
            $range = [$start, $start->copy()->endOfYear()];
        }

        if ($range) {
            $query->whereBetween('date', $range);

            return;
        }

        $like = '%'.addcslashes($value, '\%_').'%';
        $query->whereRaw("DATE_FORMAT(date, '%d-%m-%Y') LIKE ?", [$like]);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function show($id, ?User $viewer = null)
    {
        return $this->visibleTo($viewer)->whereKey($id)->first();
    }

    public function create($input)
    {
        return $this->model->create([
            'membership_code' => $input->membership_code,
            'date' => Carbon::parse($input->date),
            'correspondence_address' => $input->correspondence_address,
            'mass_attendence' => $input->mass_attendence,
            'needs' => $input->needs,
            'communication_means' => $input->communication_means,
            'attend_meetings' => $input->attend_meetings,
            'need_eftkad_from_meeting' => $input->need_eftkad_from_meeting,
            'father_confession' => $input->father_confession,
            'mother_confession' => $input->mother_confession,
            'children_confession' => $input->children_confession,
            'need_eftkad_by_father' => $input->need_eftkad_by_father ?? BoolType::FALSE,
            'location' => $input->location,
            'location_url' => $input->location_url,
            'general_notes' => $input->general_notes,
            'father_membership_code' => $input->father_membership_code,
            'servant_membership_code' => $input->servant_membership_code,
            'type' => $input->type,
        ]);
    }
}
