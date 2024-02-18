<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService extends AbstractService
{
    protected $model;


    protected $relations = ['company', 'branch', 'guarantor', 'idType'];
    /**
     * @var GuarantorService
     */
    private GuarantorService $guarantorService;
    /**
     * @var ActivityService
     */
    private ActivityService $activityService;

    /**
     * UserService constructor.
     * @param User $model
     * @param GuarantorService $guarantorService
     * @param ActivityService $activityService
     */
    public function __construct(User $model, GuarantorService $guarantorService, ActivityService $activityService)
    {
        $this->model = $model;
        $this->guarantorService = $guarantorService;
        $this->activityService = $activityService;
    }

    public function user($id)
    {
        $user = $this->getById($id, $this->relations);
        return $user ? prepareResponse(true, $user) : user_not_found();
    }

    public function users()
    {
        $users = $this->model->with($this->relations)->get();

        return prepareResponse(true, $users);
    }

    public function addUser(array $attributes)
    {
        $validData = $this->validate($attributes, ['password' => 'required|min:6',
            'username'=>'required|unique:users',
            'name' => 'required|min:3',
            'role_name' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $attributes['password'] = Hash::make($attributes['password']);

        $user = $this->store($attributes);

        if ($user == null) {
            return prepareResponse(false, ['message' => 'Sign Up Failed']);
        }


        $user->status = 1;
        $user->company_id = safe_indexing($attributes, 'company_id');
        $user->branch_id = safe_indexing($attributes, 'branch_id');
        $user->role_name = $attributes['role_name'];
        $user->save();

        $guarantorAttributes = $this->processGuarantorRequest($attributes);
        $guarantorAttributes['user_id'] = $user->id;

        $this->guarantorService->addGuarantor($guarantorAttributes);

        $_user = Auth::user();
        $this->activityService->addActivity(['company_id' => $_user->company_id, 'branch_id' => $_user->branch_id,
            'user_id' => auth_id(),
            'action' => 'Added new Officer [' . $user->name . ']' ]);

        return $this->user($user->id);
    }

    public function updateUser($id, array $attributes)
    {
        $validData = $this->validate($attributes, [
            'name' => 'required|min:3']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }


        $this->update($id, $attributes);

        $user = $this->getById($id);

        if ($user == null) {
            return prepareResponse(false, ['message' => 'Failed to Update']);
        }

        $user->status = 1;
        $user->company_id = safe_indexing($attributes, 'company_id');
        $user->branch_id = safe_indexing($attributes, 'branch_id');
        $user->role_name = $attributes['role_name'];
        $user->save();

        $guarantorAttributes = $this->processGuarantorRequest($attributes);
        $guarantorAttributes['user_id'] = $user->id;

        $this->guarantorService->addGuarantor($guarantorAttributes);

        $_user = Auth::user();
        $this->activityService->addActivity(['company_id' => $_user->company_id, 'branch_id' => $_user->branch_id,
            'user_id' => auth_id(),
            'action' => 'Updated Officer [' . $user->name . '] details' ]);

        return  $this->user($user->id);
    }

    public function changeRole($attributes)
    {
        $validData = $this->validate($attributes, ['role_name' => 'required', 'user_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }
        $user = $this->findById($attributes['user_id']);
        if ($user == null) {
            return user_not_found();
        }

        $user->role_name = $attributes['role_name'];
        $user->save();

        $_user = Auth::user();
        $this->activityService->addActivity(['company_id' => $_user->company_id, 'branch_id' => $_user->branch_id,
            'user_id' => auth_id(),
            'action' => 'Changed Officer [' . $user->name . '] Level' ]);

        return prepareResponse(true, $this->user($user->id));

    }

    public function addPicture(Request $request)
    {
        $validData = $this->validate($request->toArray(), ['file' => 'required|image', 'user_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $path = $request->file('file')->store('users');

        $user = $this->getById($request->get('user_id'));
        if ($user) {
            $user->raw_picture = $path;
            $user->save();
            return prepareResponse(true, "Picture Saved");
        }

        return prepareResponse(false, ["message" => "Failed to Save Picture"],
            Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function processGuarantorRequest(array $request)
    {

        $guarantorAttributes = collect();
        $data = collect($request)->filter(function($value, $key) {
            return !is_bool(strpos($key, 'guarantor'));
        });

        foreach ($data as $key=>$value)
        {
            $guarantorAttributes->put(explode('_', $key)[1], $value);
        }

        return $guarantorAttributes->toArray();
    }

    public function usernameExists($username)
    {
        return $this->getByWithTrash($username, 'username') != null;
    }

}
