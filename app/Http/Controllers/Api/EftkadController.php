<?php

namespace App\Http\Controllers\Api;

use App\DTOs\EftkadCreateDTO;
use App\Enums\UserRole;
use App\Http\Controllers\BaseController;
use App\Http\Requests\EftkadCreateRequest;
use App\Http\Resources\EftkadResource;
use App\Repository\EftkadRepository;
use App\Services\EftkadService;
use Illuminate\Http\Request;

class EftkadController extends BaseController
{
    public function __construct(protected EftkadService $eftkadService) {}

    public function create(EftkadCreateRequest $request)
    {
        $data = $request->only(
            'membership_code', 'date', 'correspondence_address', 'mass_attendence', 'needs',
            'communication_means', 'attend_meetings', 'need_eftkad_from_meeting', 'father_confession',
            'mother_confession', 'children_confession', 'father_confessor', 'mother_confessor', 'children_confessor', 'need_eftkad_by_father', 'location', 'location_url', 'general_notes',
            'father_membership_code', 'servant_membership_code', 'type'
        );

        // A regular user can only record visits as themselves; only admins may file on behalf of others.
        $user = $request->user();
        if (! $user->hasAnyRole(UserRole::MANAGERS)) {
            $data['servant_membership_code'] = $user->membership_code;
        }

        $input = new EftkadCreateDTO(...$data);

        $eftkad = $this->eftkadService->create($input);

        return $this->apiResponse(new EftkadResource($eftkad));
    }

    public function index(Request $request)
    {
        $data = $this->eftkadService->index(
            $request->only('father', 'servant', 'type', 'date', 'date_from', 'date_to'),
            (int) $request->query('per_page', EftkadRepository::DEFAULT_PER_PAGE),
            $request->user(),
        );

        return $this->respondResource(EftkadResource::collection($data['eftkads']),
            additional_data: [
                'eftkads_count' => $data['eftkads_count'],
            ]);
    }

    public function share(EftkadShareRequest $request, $id)
    {
        $eftkad = $this->eftkadService->share($id, $request->validated('membership_codes'), $request->user());

        if (! $eftkad) {
            return $this->apiErrorResponse(message: trans('messages.eftkad not found'), status_code: 404);
        }

        return $this->apiResponse(new EftkadResource($eftkad));
    }

    public function unshare(Request $request, $id, string $code)
    {
        $eftkad = $this->eftkadService->unshare($id, $code, $request->user());

        if (! $eftkad) {
            return $this->apiErrorResponse(message: trans('messages.eftkad not found'), status_code: 404);
        }

        return $this->apiResponse(new EftkadResource($eftkad));
    }

    public function show(Request $request, $id)
    {
        $eftkad = $this->eftkadService->show($id, $request->user());

        if (! $eftkad) {
            return $this->apiErrorResponse(message: trans('messages.eftkad not found'), status_code: 404);
        }

        return $this->apiResponse(new EftkadResource($eftkad));
    }
}
