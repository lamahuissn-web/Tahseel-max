<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 0;
    }
    .profile-card {
        max-width: 400px;
        margin: 50px auto;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.2);
    }
    .profile-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 30px;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: #fff;
    }
    .profile-img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 15px;
        border: 4px solid rgba(255, 255, 255, 0.2);
    }
    .profile-name {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }
    .profile-title {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 5px 0;
    }
    .profile-location {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.8);
    }
    .profile-stats {
        display: flex;
        justify-content: space-around;
        padding: 20px;
        background-color: #f8f9fa;
        border-top: 1px solid #e0e0e0;
    }
    .stat-item {
        text-align: center;
    }
    .stat-number {
        font-size: 1.25rem;
        font-weight: 700;
        color: #333;
    }
    .stat-label {
        font-size: 0.85rem;
        color: #666;
    }
    .profile-details {
        padding: 20px;
    }
    .detail-item {
        margin-bottom: 15px;
    }
    .detail-label {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 5px;
    }
    .detail-value {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
    }
    .social-links {
        text-align: center;
        padding: 20px;
        background-color: #f8f9fa;
        border-top: 1px solid #e0e0e0;
    }
    .social-icons {
        display: flex;
        justify-content: center;
        gap: 20px;
    }
    .social-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: #555;
        text-decoration: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
    }
    .social-icon:hover {
        background-color: #667eea;
        color: #fff;
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .social-icon .tooltip {
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #333;
        color: #fff;
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 4px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .social-icon:hover .tooltip {
        opacity: 1;
        visibility: visible;
    }



    .table {
        width: 100%;
        margin-bottom: 20px;
        border-collapse: collapse;
    }
    .table-bordered {
        border: 1px solid #e0e0e0;
    }
    .table-sm td, .table-sm th {
        padding: 12px;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.03);
    }
    .class_label {
        font-size: 0.9rem;
        color: #666;
    }
    .class_result {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
    }
</style>

<div class="profile-card" style="margin-top: -20px">
    <div class="profile-header">
        <h2 class="profile-name">{{ $all_data->name }}</h2>
    </div>

    <div class="profile-stats">
        <div class="stat-item">
            <div class="stat-number">{{$projects_data->count()}}</div>
            <div class="stat-label"><?= trans('company.projects') ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{$clients_data->count()}}</div>
            <div class="stat-label"><?= trans('company.clients') ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{$tests_data->count()}}</div>
            <div class="stat-label"><?= trans('company.tests') ?></div>
        </div>
    </div>
    <table class="table table-bordered table-sm table-striped">
        <tbody>
        <tr>
            <td class="class_label" style="width: 25%"><?= trans('company.company_code') ?></td>
            <td class="class_result">{{ $all_data->company_code }}</td>
        </tr>
        <tr>
            <td class="class_label" style="width: 25%"><?= trans('company.name') ?></td>
            <td class="class_result">{{ $all_data->name  }}</td>
        </tr>
        <tr>
            <td class="class_label" style="width: 25%"><?= trans('company.email') ?></td>
            <td class="class_result">{{ $all_data->email  }}</td>
        </tr>
        <tr>
            <td class="class_label" style="width: 25%"><?= trans('company.phone') ?></td>
            <td class="class_result">{{ $all_data->phone }}</td>
        </tr>
        <tr>
            <td class="class_label" style="width: 25%"><?= trans('company.client') ?></td>
            <td class="class_result">{{ $all_data->client->name }}</td>
        </tr>
        </tbody>
    </table>
    <div class="social-links">
        <div class="social-icons">
            <a href="tel:{{$all_data->phone}}" class="social-icon" style="background-color: forestgreen">
                <i class="bi bi-phone"></i>
                <span class="tooltip">Call</span>
            </a>
            <a href="mailto:{{$all_data->email}}" class="social-icon"  style="background-color: lightcoral">
                <i class="bi bi-envelope"></i>
                <span class="tooltip">Email</span>
            </a>
            <a href="https://wa.me/{{$all_data->phone}}" class="social-icon" style="background-color: forestgreen">
                <i class="bi bi-whatsapp"></i>
                <span class="tooltip">WhatsApp</span>
            </a>
        </div>
    </div>
</div>
