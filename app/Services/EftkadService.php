<?php

namespace App\Services;

use App\Repository\EftkadRepository;
use Illuminate\Support\Facades\DB;

class EftkadService
{
    public function __construct(
        protected EftkadRepository $eftkadRepository,

    ) {
        if (request()->route()->parameter('all') == 'all') {
            $this->eftkadRepository->pagination = false;
        }
    }

    public function index()
    {
        $eftkads = $this->eftkadRepository->index();
        $eftkads_count = method_exists('total', $eftkads) ? $eftkads->total() : $eftkads->count();

        return compact('eftkads', 'eftkads_count');
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
