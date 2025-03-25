<?php

namespace App\Repository;

use App\Models\Eftkad;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EftkadRepository
{
    public function __construct(protected Eftkad $model) {}

    public bool $pagination = true;

    public int $perPage = 10;

    protected function execute(Builder $query): Collection|LengthAwarePaginator
    {
        return $this->pagination ? $query->paginate($this->perPage) : $query->get();
    }

    public function index()
    {
        $query = $this->model->query();

        return $this->execute($query);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function show($id)
    {
        $eftkad = $this->find($id);

        return $eftkad;
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
            'need_eftkad_by_father' => $input->need_eftkad_by_father,
            'location' => $input->location,
            'general_notes' => $input->general_notes,
        ]);
    }
}
