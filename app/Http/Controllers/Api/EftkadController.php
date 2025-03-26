<?php

namespace App\Http\Controllers\Api;

use App\DTOs\EftkadCreateDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\EftkadCreateRequest;
use App\Http\Resources\EftkadResource;
use App\Services\EftkadService;

class EftkadController extends BaseController
{
    public function __construct(protected EftkadService $eftkadService) {}

    public function create(EftkadCreateRequest $request)
    {
        $input = new EftkadCreateDTO(...$request->only(
            'membership_code', 'date', 'correspondence_address', 'mass_attendence', 'needs',
            'communication_means', 'attend_meetings', 'need_eftkad_from_meeting', 'father_confession',
            'mother_confession', 'children_confession', 'need_eftkad_by_father', 'location', 'general_notes',
            'father_membership_code', 'servant_membership_code', 'type'
        ));

        $eftkad = $this->eftkadService->create($input);

        return $this->apiResponse(new EftkadResource($eftkad));
    }

    public function index()
    {
        $data = $this->eftkadService->index();

        return $this->respondResource(EftkadResource::collection($data['eftkads']),
            additional_data: [
                'eftkads_count' => $data['eftkads_count'],
            ]);
    }
}
