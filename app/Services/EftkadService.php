<?php

namespace App\Services;

use App\Models\User;
use App\Repository\EftkadRepository;
use Illuminate\Support\Facades\DB;

class EftkadService
{
    public function __construct(
        protected EftkadRepository $eftkadRepository,
    ) {}

    public function index(array $filters = [], int $perPage = EftkadRepository::DEFAULT_PER_PAGE, ?User $viewer = null)
    {
        $eftkads = $this->eftkadRepository->index($filters, $perPage, $viewer);

        return ['eftkads' => $eftkads, 'eftkads_count' => $eftkads->total()];
    }

    public function show($id, ?User $viewer = null)
    {
        $eftkad = $this->eftkadRepository->show($id, $viewer)?->load('shares');

        // Opening a visit clears its "new" flag on the viewer's home page.
        if ($eftkad && $viewer?->membership_code) {
            $this->eftkadRepository->markSeen($eftkad, $viewer->membership_code);
        }

        return $eftkad;
    }

    public function home(User $viewer): array
    {
        return $this->eftkadRepository->home($viewer);
    }

    /** The person who files a visit has obviously seen it. */
    public function markSeen($eftkad, User $viewer): void
    {
        if ($viewer->membership_code) {
            $this->eftkadRepository->markSeen($eftkad, $viewer->membership_code);
        }
    }

    /** Who a visit is shared with, without counting as the viewer opening it. */
    public function shareInfo($id, User $viewer)
    {
        return $this->eftkadRepository->show($id, $viewer)?->load('shares');
    }

    public function share($id, array $codes, User $viewer)
    {
        $eftkad = $this->eftkadRepository->show($id, $viewer);

        if ($eftkad) {
            $this->eftkadRepository->share($eftkad, $codes, $viewer->membership_code);
            $eftkad->load('shares');
        }

        return $eftkad;
    }

    public function unshare($id, string $code, User $viewer)
    {
        $eftkad = $this->eftkadRepository->show($id, $viewer);

        if ($eftkad) {
            $this->eftkadRepository->unshare($eftkad, $code);
            $eftkad->load('shares');
        }

        return $eftkad;
    }

    public function create($input)
    {
        return DB::transaction(fn () => $this->eftkadRepository->create($input));
    }
}
