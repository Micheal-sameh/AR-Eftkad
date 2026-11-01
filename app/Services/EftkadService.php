<?php

namespace App\Services;

use App\Repository\EftkadRepository;
use Illuminate\Support\Facades\DB;

class EftkadService
{
    public function __construct(
        protected EftkadRepository $eftkadRepository,
    ) {}

    public function index(array $filters = [], int $perPage = EftkadRepository::DEFAULT_PER_PAGE)
    {
        $eftkads = $this->eftkadRepository->index($filters, $perPage);

        return ['eftkads' => $eftkads, 'eftkads_count' => $eftkads->total()];
    }

    public function show($id)
    {
        $eftkad = $this->eftkadRepository->show($id);

        return $eftkad;
    }

    public function create($input)
    {
        DB::beginTransaction();
        $eftkad = $this->eftkadRepository->create($input);
        DB::commit();

        return $eftkad;
    }
}
