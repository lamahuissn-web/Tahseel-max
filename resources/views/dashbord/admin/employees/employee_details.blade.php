<div class="card shadow  bg-white rounded">
    <div class="card-header" style="background-color: #f8f9fa;">
        <h3 class="card-title"><i class="fas fa-text-width"></i> <?= trans('employees.employee_details') ?></h3>
    </div>
    <div class="card-body" style="padding: 20px !important;">
        <table class="table table-bordered table-sm table-striped" >
            <tbody>
            <tr>
                <td class="class_label" style="width: 25%"><?= trans('employees.profile_picture') ?></td>
                <td class="class_result">
                    <img src="{{ asset('images/'.$all_data->profile_picture) }}" alt="<?= trans('employees.profile_picture') ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                </td>
            </tr>
            <tr>
                <td class="class_label" style="width: 25%"><?= trans('employees.employee') ?></td>
                <td class="class_result">{{ $all_data->first_name.' '.$all_data->last_name  }}</td>
            </tr>
            <tr>
                <td class="class_label" style="width: 25%"><?= trans('employees.employee_code') ?></td>
                <td class="class_result">{{ $all_data->emp_code }}</td>
            </tr>
            {{-- <tr>
                <td class="class_label" style="width: 25%"><?= trans('employees.email') ?></td>
                <td class="class_result">{{ $all_data->email }}</td>
            </tr>
            <tr>
                <td class="class_label" style="width: 25%"><?= trans('employees.national_id') ?></td>
                <td class="class_result">{{ $all_data->national_id }}</td>
            </tr>
            <tr>
                <td class="class_label"><?= trans('employees.gender') ?></td>
                <td class="class_result">{{ trans('employees.'.$all_data->gender) }}</td>
            </tr> --}}
            <tr>
                <td class="class_label"><?= trans('employees.position') ?></td>
                <td class="class_result">{{ $all_data->position }}</td>
            </tr>
            <tr>
                <td class="class_label"><?= trans('employees.salary') ?></td>
                <td class="class_result">{{ $all_data->salary }}</td>
            </tr>
            <tr >
                <td class="class_label"><?= trans('employees.hire_date') ?></td>
                <td class="class_result">{{ $all_data->hire_date }}</td>
            </tr>
            <tr >
                <td class="class_label"><?= trans('employees.phone') ?></td>
                <td class="class_result">{{ $all_data->phone }}</td>
            </tr>
            <tr >
                <td class="class_label"><?= trans('employees.whatsapp_num') ?></td>
                <td class="class_result">{{ $all_data->whatsapp_num }}</td>
            </tr>
            {{-- <tr>
                <td class="class_label"><?= trans('employees.date_of_birth') ?></td>
                <td class="class_result">{{ $all_data->date_of_birth }}</td>
            </tr> --}}
            <tr>
                <td class="class_label"><?= trans('employees.address') ?></td>
                <td class="class_result">{{ $all_data->address }}</td>
            </tr>
            {{-- <tr>
                <td class="class_label"><?= trans('employees.material_status') ?></td>
                <td class="class_result">{{ trans('employees.'.$all_data->material_status) }}</td>
            </tr>
            <tr>
                <td class="class_label"><?= trans('employees.religion') ?></td>
                <td class="class_result">{{ trans('employees.'.$all_data->religion) }}</td>
            </tr> --}}
            </tbody>
        </table>
    </div>
</div>
