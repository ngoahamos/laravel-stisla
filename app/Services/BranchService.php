<?php

namespace App\Services;

use App\Models\Branch;

class BranchService extends AbstractService
{
    protected $model;
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * BranchService constructor.
     * @param Branch $model
     * @param ActivityService $activityService
     */
    public function __construct(Branch $model, ActivityService $activityService)
    {
        $this->model = $model;
        $this->activityService = $activityService;
    }

    public function branch($id)
    {
        $branch = $this->getById($id);

        return prepareResponse(true, $branch);
    }

    public function branches()
    {
        $branches = $this->all();

        return prepareResponse(true, $branches);
    }


    public function addBranch(array $attributes)
    {
        $validData = $this->validate($attributes,['name' => 'required','company_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $branch = $this->store($attributes);

        if ($branch == null)
        {
            return $this->storeFailed("Failed to Add Branch");
        }

        $this->activityService->addActivity(['company_id' => $branch->company_id, 'branch_id' => $branch->id,
            'user_id' => auth_id(),
            'action' => 'Added Branch [' . $branch->name . ']']);

        return $this->branch($branch->id);
    }

    public function updateBranch($id, array $attributes)
    {
        $branch = $this->getById($id);

        if ($branch == null)
        {
            return $this->notFound("Branch not Found");
        }

        $this->update($id, $attributes);

        $this->activityService->addActivity(['company_id' => $branch->company_id, 'branch_id' => $branch->id,
            'user_id' => auth_id(),
            'action' => 'Updated Branch [' . $branch->name . ']']);

        return $this->branch($id);
    }
}
