<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class CompanyService extends AbstractService
{
    protected $model;
    /**
     * @var CredentialService
     */
    private $credentialService;

    /**
     * CompanyService constructor.
     * @param Company $model
     * @param CredentialService $credentialService
     */
    public function __construct(Company $model,
                                CredentialService $credentialService)
    {
        $this->model = $model;
        $this->credentialService = $credentialService;
    }

    public function company($id)
    {
        $company = $this->getById($id);

        if ($company == null)
        {
            return $this->notFound("Company Not Found");
        }

        return prepareResponse(true, $company);
    }

    public function companies()
    {
        $companies = $this->all([], true);

        return prepareResponse(true, $companies);
    }

    public function addCompany(array $attributes)
    {
        $validData = $this->validate($attributes,['name' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $company = $this->store($attributes);

        if ($company == null)
        {
            return $this->storeFailed("Failed to add Company");
        }

        return $this->company($company->id);
    }

    public function addCompanyWithUser(array $attributes)
    {
        $validData = $this->validate($attributes,['company_name' => 'required','password' => 'required',
            'username' => 'required|unique:users', 'name' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $company = $this->store(['name' => $attributes['company_name'],
            'telephone' => safe_indexing($attributes, 'telephone'),
            'address' => safe_indexing($attributes, 'address'),
            'color' => safe_indexing($attributes, 'color')
        ]);

        if ($company == null)
        {

            return $this->storeFailed("Failed to add company");
        }

        $user = new User;
        $user->username = $attributes['username'];
        $user->name = $attributes['name'];
        $user->role_name = 'director';
        $user->password = Hash::make($attributes['password']);
        $user->company_id = $company->id;

        $user->save();

        return prepareResponse(true, "Company Created");

    }

    public function updateCompany($id, array $attributes)
    {
        $company = $this->getById($id);

        if ($company == null)
        {
            return $this->notFound('Company Not Found');
        }

        $this->update($id, $attributes);

        return $this->company($id);
    }

    /**
     * Disables company account
     *
     * @param $company_id
     * @return \stdClass
     */
    public function disableCompany($company_id)
    {
        $company = $this->getById($company_id);

        if ($company == null)
        {
            return $this->notFound('Company Not Found');
        }

        $this->credentialService->blockCompanyUsers($company_id);

        $this->deleteInstance($company_id);

        return prepareResponse(true, "Company's Account Disabled");
    }

    public function restoreCompany($company_id)
    {
        $company = $this->getByWithTrash($company_id);

        if ($company == null)
        {
            return $this->notFound('Company Not Found');
        }

        $this->credentialService->restoreCompanyUsers($company_id);

        $this->restoreInstance($company_id);

        return prepareResponse(true, "Company's Account Restored");
    }

    public function addLogo(Request $request)
    {
        $validData = $this->validate($request->toArray(), ['file' => 'required|file', 'company_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $path = $request->file('file')->store('companies');

        $company = $this->getById($request->get('company_id'));
        if ($company) {
            $company->raw_logo = $path;
            $company->save();
            return prepareResponse(true, "Logo Saved");
        }

        return prepareResponse(false, ["message" => "Failed to Save Logo"],
            Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
