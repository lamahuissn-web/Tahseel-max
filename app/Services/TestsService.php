<?php


namespace App\Services;


use App\Interfaces\BasicRepositoryInterface;
use App\Models\Admin\Test;
use App\Traits\ImageProcessing;

class TestsService
{

    use ImageProcessing;
    protected $TestsRepository;
    public function __construct(BasicRepositoryInterface $basicRepository)
    {
        $this->TestsRepository   = createRepository($basicRepository, new Test());
    }
    /************************************************/
    public function store($request)
    {
        $validated_data = $request->validated();
        $validated_data['client_id'] = $request->client_id;
        $validated_data['company_id'] = $request->company_id;
        $validated_data['project_id'] = $request->project_id;
        $validated_data['test_code'] = $request->test_code;
        $validated_data['talab_number'] = $request->talab_number;
        $validated_data['talab_title'] = $request->talab_title;
        $validated_data['talab_date'] = $request->talab_date;
        $validated_data['talab_end_date'] = $request->talab_end_date;
        $validated_data['created_by'] = auth()->user()->id;

        if ($request->hasFile('talab_image')) {
            $validated_data['talab_image'] = $this->saveImage($request->file('talab_image'), 'tests_talabat');
        }

        return $this->TestsRepository->create($validated_data);
    }

    /************************************************/
    public function update($request, $id)
    {
        $validated_data = $request->validated();
        $validated_data['client_id'] = $request->client_id;
        $validated_data['company_id'] = $request->company_id;
        $validated_data['project_id'] = $request->project_id;
        $validated_data['test_code'] = $request->test_code;
        $validated_data['talab_number'] = $request->talab_number;
        $validated_data['talab_title'] = $request->talab_title;
        $validated_data['talab_date'] = $request->talab_date;
        $validated_data['talab_end_date'] = $request->talab_end_date;
        $validated_data['updated_by'] = auth()->user()->id;

        $test = $this->TestsRepository->getById($id);
        if ($request->hasFile('talab_image')) {
            if ($test->talab_image) {
                $oldImagePath = public_path('images/' . $test->talab_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $validated_data['talab_image'] = $this->saveImage($request->file('talab_image'), 'tests_talabat');
        }

        return $this->TestsRepository->update($id, $validated_data);
    }
    /**************************************************/




}
