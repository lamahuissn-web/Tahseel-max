<style>
    .member-info-card {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 24px;
        max-width: 100%;
        margin: 0 auto;
    }

    .card-title {
        font-size: 24px;
        color: #333;
        margin-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 8px;
    }

    .member-name {
        font-size: 20px;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 14px;
        color: #7f8c8d;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 16px;
        color: #2c3e50;
        font-weight: 500;
    }
</style>
<div class="member-info-card">
    <h2 class="card-title">{{trans('members.member_name')}}</h2>
    <div class="member-name">{{ $one_data->member->member_name }}</div>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">{{trans('members.date')}}</span>
            <span class="info-value">{{ $one_data->date }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">{{trans('members.height')}}</span>
            <span class="info-value">{{ $one_data->height }} cm</span>
        </div>
        <div class="info-item">
            <span class="info-label">{{trans('members.weight')}}</span>
            <span class="info-value">{{ $one_data->weight }} kg</span>
        </div>
        <div class="info-item">
            <span class="info-label">{{trans('members.fat_percentage')}}</span>
            <span class="info-value">{{ $one_data->fat_percentage }}%</span>
        </div>
        <div class="info-item">
            <span class="info-label">{{trans('members.muscle_mass_percentage')}}</span>
            <span class="info-value">{{ $one_data->muscle_mass_percentage }}%</span>
        </div>
        <div class="info-item">
            <span class="info-label">{{trans('members.body_status')}}</span>
            <span class="info-value">{{ $one_data->body_status }}</span>
        </div>
    </div>
</div>
