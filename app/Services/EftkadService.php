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
        return $this->eftkadRepository->show($id, $viewer);
    }

    public function create($input)
    {
        return DB::transaction(fn () => $this->eftkadRepository->create($input));
    }
}
