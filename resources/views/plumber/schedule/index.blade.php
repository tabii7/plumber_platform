@extends('layouts.modern-dashboard')

@section('title', 'Schedule Management')

@section('page-title', 'Schedule Management')

@section('sidebar-nav')
    <div class="nav-item">
        <a href="{{ route('plumber.dashboard') }}" class="nav-link">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('plumber.coverage.index') }}" class="nav-link">
            <i class="fas fa-map-marker-alt"></i>
            <span>Coverage Areas</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('plumber.schedule.index') }}" class="nav-link active">
            <i class="fas fa-clock"></i>
            <span>Schedule</span>
        </a>
    </div>
    

    
    <div class="nav-item">
        <a href="{{ route('support') }}" class="nav-link">
            <i class="fas fa-headset"></i>
            <span>Support</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('profile.edit') }}" class="nav-link">
            <i class="fas fa-user-cog"></i>
            <span>Profile</span>
        </a>
    </div>
@endsection

@section('content')
<style>
.schedule-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
    padding: 2rem;
}

.schedule-header {
    background: linear-gradient(135deg, #344767 0%, #1a1a1a 100%);
    color: white;
    padding: 2.5rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.header-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.header-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: white;
}

.header-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.schedule-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 2rem;
}

.card-header-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 2rem;
    border-bottom: 1px solid #e9ecef;
}

.card-header-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.card-header-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #172b4d;
    margin-bottom: 0.5rem;
}

.card-header-subtitle {
    color: #6c757d;
    margin: 0;
}

.card-body-section {
    padding: 2rem;
}

.day-schedule-card {
    background: #f8f9fa;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.day-schedule-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.day-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.day-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.day-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.day-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #172b4d;
    margin: 0;
}

.day-subtitle {
    color: #6c757d;
    font-size: 0.875rem;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-badge.closed {
    background: #fee2e2;
    color: #dc2626;
}

.status-badge.open24 {
    background: #dcfce7;
    color: #16a34a;
}

.status-badge.split {
    background: #dbeafe;
    color: #2563eb;
}

.status-badge.fullday {
    background: #fef3c7;
    color: #d97706;
}

.mode-selector {
    margin-bottom: 1.5rem;
}

.mode-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}

.mode-option {
    cursor: pointer;
    border: 2px solid #e9ecef;
    border-radius: 0.75rem;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s ease;
    background: white;
}

.mode-option:hover {
    border-color: #06b6d4;
    transform: translateY(-2px);
}

.mode-option.active {
    border-color: #06b6d4;
    background: #f0f9ff;
}

.mode-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.mode-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
}

.mode-icon.closed {
    background: #fee2e2;
    color: #dc2626;
}

.mode-icon.open24 {
    background: #dcfce7;
    color: #16a34a;
}

.mode-icon.split {
    background: #dbeafe;
    color: #2563eb;
}

.mode-icon.fullday {
    background: #fef3c7;
    color: #d97706;
}

.time-inputs {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
}

.time-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.time-input-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.time-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

.time-input {
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.time-input:focus {
    outline: none;
    border-color: #06b6d4;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
}

.save-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 2rem;
    border-radius: 1rem;
    text-align: center;
    margin-top: 2rem;
}

.btn-save {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 0.75rem;
    font-size: 1.125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(6, 182, 212, 0.3);
}

.save-hint {
    color: #6c757d;
    margin: 1rem 0 0 0;
    font-size: 0.875rem;
}

.sidebar-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    overflow: hidden;
}

.sidebar-card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.sidebar-card-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.sidebar-card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #172b4d;
    margin-bottom: 0.5rem;
}

.sidebar-card-subtitle {
    color: #6c757d;
    margin: 0;
}

.sidebar-card-body {
    padding: 1.5rem;
}

.holiday-item, .vacation-item {
    background: #f8f9fa;
    border-radius: 0.75rem;
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
}

.holiday-input, .date-input, .note-input {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.holiday-input:focus, .date-input:focus, .note-input:focus {
    outline: none;
    border-color: #06b6d4;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
}

.holiday-remove, .vacation-remove {
    background: #fee2e2;
    color: #dc2626;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 0.5rem;
}

.holiday-remove:hover, .vacation-remove:hover {
    background: #fecaca;
    transform: scale(1.05);
}

.btn-add-holiday, .btn-add-vacation {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.btn-add-holiday:hover, .btn-add-vacation:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(6, 182, 212, 0.3);
}

.vacation-dates {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.date-input-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.date-label, .note-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #374151;
}

.info-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 1rem;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.info-item:last-child {
    margin-bottom: 0;
}

.success-alert {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border: 1px solid #16a34a;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.alert-content {
    color: #166534;
    font-weight: 600;
}

.alert-close {
    background: none;
    border: none;
    color: #16a34a;
    cursor: pointer;
    font-size: 1.125rem;
}

@media (max-width: 768px) {
    .schedule-container {
        padding: 1rem;
    }
    
    .header-title {
        font-size: 2rem;
    }
    
    .mode-options {
        grid-template-columns: 1fr;
    }
    
    .time-grid {
        grid-template-columns: 1fr;
    }
    
    .vacation-dates {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="schedule-container">
    <!-- Header Section -->
    <div class="schedule-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="header-badge">
                    <i class="fas fa-clock me-2"></i>
                    <span>Weekly Schedule</span>
                </div>
                <h1 class="header-title">Manage Your Availability</h1>
                <p class="header-subtitle">Set your working hours, holidays, and vacation periods to help clients know when you're available.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-light btn-lg" id="exportJson">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                    <button class="btn btn-outline-light btn-lg" id="resetDefaults">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="success-alert">
            <div class="alert-content">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
            <button type="button" class="alert-close" data-bs-dismiss="alert">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <form id="scheduleForm">
        @csrf
        <div class="row">
            <!-- Main Schedule -->
            <div class="col-lg-8">
                <div class="schedule-card">
                    <div class="card-header-section">
                        <div class="card-header-icon">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div>
                            <h3 class="card-header-title">Weekly Schedule</h3>
                            <p class="card-header-subtitle">Set your availability for each day of the week</p>
                        </div>
                    </div>
                    
                    <div class="card-body-section">
                        @php
                            $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 
                                    'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
                            $scheduleData = $schedule->schedule_data ?? [];
                        @endphp
                        
                        @foreach($days as $key => $label)
                            @php
                                $day = $scheduleData[$key] ?? ['mode' => 'split', 'split' => ['o1' => '09:00', 'c1' => '12:00', 'o2' => '13:30', 'c2' => '19:00'], 'full' => ['o' => '09:00', 'c' => '19:00']];
                                $mode = $day['mode'] ?? 'split';
                                $split = $day['split'] ?? ['o1' => '09:00', 'c1' => '12:00', 'o2' => '13:30', 'c2' => '19:00'];
                                $full = $day['full'] ?? ['o' => '09:00', 'c' => '19:00'];
                            @endphp
                            
                            <div class="day-schedule-card" data-day="{{ $key }}">
                                <div class="day-header">
                                    <div class="day-info">
                                        <div class="day-icon">
                                            <i class="fas fa-sun"></i>
                                        </div>
                                        <div>
                                            <h4 class="day-title">{{ $label }}</h4>
                                            <span class="day-subtitle">24-hour format</span>
                                        </div>
                                    </div>
                                    <div class="day-status {{ $mode }}">
                                        @if($mode === 'closed')
                                            <span class="status-badge closed">
                                                <i class="fas fa-times-circle me-1"></i>Closed
                                            </span>
                                        @elseif($mode === 'open24')
                                            <span class="status-badge open24">
                                                <i class="fas fa-clock me-1"></i>24h Open
                                            </span>
                                        @elseif($mode === 'split')
                                            <span class="status-badge split">
                                                <i class="fas fa-clock me-1"></i>Split Hours
                                            </span>
                                        @else
                                            <span class="status-badge fullday">
                                                <i class="fas fa-clock me-1"></i>Full Day
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Mode Selection -->
                                <div class="mode-selector">
                                    <div class="mode-options">
                                        <label class="mode-option {{ $mode === 'closed' ? 'active' : '' }}">
                                            <input type="radio" name="schedule_data[{{ $key }}][mode]" value="closed" {{ $mode === 'closed' ? 'checked' : '' }} style="display: none;">
                                            <div class="mode-content">
                                                <div class="mode-icon closed">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                                <span>Closed</span>
                                            </div>
                                        </label>
                                        
                                        <label class="mode-option {{ $mode === 'open24' ? 'active' : '' }}">
                                            <input type="radio" name="schedule_data[{{ $key }}][mode]" value="open24" {{ $mode === 'open24' ? 'checked' : '' }} style="display: none;">
                                            <div class="mode-content">
                                                <div class="mode-icon open24">
                                                    <i class="fas fa-infinity"></i>
                                                </div>
                                                <span>24h Open</span>
                                            </div>
                                        </label>
                                        
                                        <label class="mode-option {{ $mode === 'split' ? 'active' : '' }}">
                                            <input type="radio" name="schedule_data[{{ $key }}][mode]" value="split" {{ $mode === 'split' ? 'checked' : '' }} style="display: none;">
                                            <div class="mode-content">
                                                <div class="mode-icon split">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <span>Split Hours</span>
                                            </div>
                                        </label>
                                        
                                        <label class="mode-option {{ $mode === 'fullday' ? 'active' : '' }}">
                                            <input type="radio" name="schedule_data[{{ $key }}][mode]" value="fullday" {{ $mode === 'fullday' ? 'checked' : '' }} style="display: none;">
                                            <div class="mode-content">
                                                <div class="mode-icon fullday">
                                                    <i class="fas fa-sun"></i>
                                                </div>
                                                <span>Full Day</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Split Schedule Row -->
                                <div class="time-inputs split-row" style="display: {{ $mode === 'split' ? 'block' : 'none' }}">
                                    <div class="time-grid">
                                        <div class="time-input-group">
                                            <label class="time-label">Morning Start</label>
                                            <input type="time" class="time-input" step="300" 
                                                   name="schedule_data[{{ $key }}][split][o1]" 
                                                   value="{{ $split['o1'] }}">
                                        </div>
                                        <div class="time-input-group">
                                            <label class="time-label">Morning End</label>
                                            <input type="time" class="time-input" step="300" 
                                                   name="schedule_data[{{ $key }}][split][c1]" 
                                                   value="{{ $split['c1'] }}">
                                        </div>
                                        <div class="time-input-group">
                                            <label class="time-label">Afternoon Start</label>
                                            <input type="time" class="time-input" step="300" 
                                                   name="schedule_data[{{ $key }}][split][o2]" 
                                                   value="{{ $split['o2'] }}">
                                        </div>
                                        <div class="time-input-group">
                                            <label class="time-label">Afternoon End</label>
                                            <input type="time" class="time-input" step="300" 
                                                   name="schedule_data[{{ $key }}][split][c2]" 
                                                   value="{{ $split['c2'] }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Full Day Row -->
                                <div class="time-inputs full-row" style="display: {{ $mode === 'fullday' ? 'block' : 'none' }}">
                                    <div class="time-grid">
                                        <div class="time-input-group">
                                            <label class="time-label">Start Time</label>
                                            <input type="time" class="time-input" step="300" 
                                                   name="schedule_data[{{ $key }}][full][o]" 
                                                   value="{{ $full['o'] }}">
                                        </div>
                                        <div class="time-input-group">
                                            <label class="time-label">End Time</label>
                                            <input type="time" class="time-input" step="300" 
                                                   name="schedule_data[{{ $key }}][full][c]" 
                                                   value="{{ $full['c'] }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="save-section">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save me-2"></i>Save Schedule
                            </button>
                            <p class="save-hint">
                                "Split Hours" = Morning (09:00–12:00) and Afternoon (13:30–19:00). 
                                "Full Day" = Single time slot (09:00–19:00). All times are 24-hour format.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Holidays Card -->
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <div class="sidebar-card-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div>
                            <h4 class="sidebar-card-title">Holidays</h4>
                            <p class="sidebar-card-subtitle">Mark specific dates as unavailable</p>
                        </div>
                    </div>
                    
                    <div class="sidebar-card-body">
                        <div class="holiday-list" id="holidayList">
                            @if(!empty($schedule->holidays))
                                @foreach($schedule->holidays as $holiday)
                                    <div class="holiday-item">
                                        <input type="date" class="holiday-input" name="holidays[]" value="{{ $holiday }}">
                                        <button type="button" class="holiday-remove" title="Remove holiday">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                <div class="holiday-item">
                                    <input type="date" class="holiday-input" name="holidays[]" value="">
                                    <button type="button" class="holiday-remove" title="Remove holiday">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn-add-holiday" id="addHoliday">
                            <i class="fas fa-plus me-2"></i>Add Holiday
                        </button>
                    </div>
                </div>

                <!-- Vacations Card -->
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <div class="sidebar-card-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div>
                            <h4 class="sidebar-card-title">Vacations</h4>
                            <p class="sidebar-card-subtitle">Set date ranges for time off</p>
                        </div>
                    </div>
                    
                    <div class="sidebar-card-body">
                        <div class="vacation-list" id="vacList">
                            @if(!empty($schedule->vacations))
                                @foreach($schedule->vacations as $vacation)
                                    <div class="vacation-item">
                                        <div class="vacation-dates">
                                            <div class="date-input-group">
                                                <label class="date-label">From</label>
                                                <input type="date" class="date-input" name="vacations[{{ $loop->index }}][from]" value="{{ $vacation['from'] }}">
                                            </div>
                                            <div class="date-input-group">
                                                <label class="date-label">To</label>
                                                <input type="date" class="date-input" name="vacations[{{ $loop->index }}][to]" value="{{ $vacation['to'] }}">
                                            </div>
                                        </div>
                                        <div class="vacation-note">
                                            <label class="note-label">Note (optional)</label>
                                            <input type="text" class="note-input" name="vacations[{{ $loop->index }}][note]" value="{{ $vacation['note'] ?? '' }}" placeholder="Vacation note">
                                        </div>
                                        <button type="button" class="vacation-remove" title="Remove vacation">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                <div class="vacation-item">
                                    <div class="vacation-dates">
                                        <div class="date-input-group">
                                            <label class="date-label">From</label>
                                            <input type="date" class="date-input" name="vacations[0][from]" value="">
                                        </div>
                                        <div class="date-input-group">
                                            <label class="date-label">To</label>
                                            <input type="date" class="date-input" name="vacations[0][to]" value="">
                                        </div>
                                    </div>
                                    <div class="vacation-note">
                                        <label class="note-label">Note (optional)</label>
                                        <input type="text" class="note-input" name="vacations[0][note]" value="" placeholder="Vacation note">
                                    </div>
                                    <button type="button" class="vacation-remove" title="Remove vacation">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn-add-vacation" id="addVac">
                            <i class="fas fa-plus me-2"></i>Add Vacation
                        </button>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="info-card">
                    <div class="info-item">
                        <i class="fas fa-globe me-2"></i>
                        <span><strong>Timezone:</strong> {{ $schedule->timezone ?? 'Europe/Brussels' }}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock me-2"></i>
                        <span><strong>Last updated:</strong> {{ $schedule->last_updated ? $schedule->last_updated->format('Y-m-d H:i') : 'Never' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Confirm Delete Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete item?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Yes, delete</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let vacationIndex = {{ !empty($schedule->vacations) ? count($schedule->vacations) : 1 }};
    let pendingDelete = null;

    // Mode behavior + show/hide time inputs
    function applyMode(card) {
        const selected = card.querySelector('input[type="radio"]:checked')?.value || 'closed';
        const splitRow = card.querySelector('.split-row');
        const fullRow = card.querySelector('.full-row');

        // Hide all rows first
        splitRow.style.display = 'none';
        fullRow.style.display = 'none';

        if (selected === 'closed') {
            // No rows shown for closed
        } else if (selected === 'open24') {
            // No rows shown for 24h
        } else if (selected === 'split') {
            splitRow.style.display = 'block';
        } else if (selected === 'fullday') {
            fullRow.style.display = 'block';
        }
    }

    // Apply mode to all day cards
    document.querySelectorAll('.day-schedule-card').forEach(card => {
        applyMode(card);
        card.addEventListener('change', ev => {
            if (ev.target.name.startsWith('schedule_data') && ev.target.name.includes('[mode]')) {
                applyMode(card);
            }
        });
    });

    // Add holiday
    document.getElementById('addHoliday').addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'holiday-item';
        row.innerHTML = `
            <input type="date" class="holiday-input" name="holidays[]" value="">
            <button type="button" class="holiday-remove" title="Remove holiday">
                <i class="fas fa-times"></i>
            </button>
        `;
        document.getElementById('holidayList').appendChild(row);
    });

    // Add vacation
    document.getElementById('addVac').addEventListener('click', () => {
        const item = document.createElement('div');
        item.className = 'vacation-item';
        item.innerHTML = `
            <div class="vacation-dates">
                <div class="date-input-group">
                    <label class="date-label">From</label>
                    <input type="date" class="date-input" name="vacations[${vacationIndex}][from]" value="">
                </div>
                <div class="date-input-group">
                    <label class="date-label">To</label>
                    <input type="date" class="date-input" name="vacations[${vacationIndex}][to]" value="">
                </div>
            </div>
            <div class="vacation-note">
                <label class="note-label">Note (optional)</label>
                <input type="text" class="note-input" name="vacations[${vacationIndex}][note]" value="" placeholder="Vacation note">
            </div>
            <button type="button" class="vacation-remove" title="Remove vacation">
                <i class="fas fa-times"></i>
            </button>
        `;
        document.getElementById('vacList').appendChild(item);
        vacationIndex++;
    });

    // Remove holiday/vacation
    document.addEventListener('click', e => {
        if (e.target.classList.contains('holiday-remove') || e.target.closest('.holiday-remove')) {
            const target = e.target.classList.contains('holiday-remove') ? e.target : e.target.closest('.holiday-remove');
            pendingDelete = target.closest('.holiday-item');
            new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
        }
        if (e.target.classList.contains('vacation-remove') || e.target.closest('.vacation-remove')) {
            const target = e.target.classList.contains('vacation-remove') ? e.target : e.target.closest('.vacation-remove');
            pendingDelete = target.closest('.vacation-item');
            new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
        }
    });

    // Confirm delete
    document.getElementById('confirmDelete').addEventListener('click', () => {
        if (pendingDelete) {
            pendingDelete.remove();
            pendingDelete = null;
        }
        bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
    });

    // Form submission
    document.getElementById('scheduleForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = {};
        
        // Convert FormData to structured object
        for (let [key, value] of formData.entries()) {
            if (key.includes('[')) {
                const parts = key.match(/(\w+)\[([^\]]+)\](?:\[([^\]]+)\])?/);
                if (parts) {
                    const [, main, sub, subsub] = parts;
                    if (!data[main]) data[main] = {};
                    if (subsub) {
                        if (!data[main][sub]) data[main][sub] = {};
                        data[main][sub][subsub] = value;
                    } else {
                        data[main][sub] = value;
                    }
                }
            } else {
                data[key] = value;
            }
        }

        try {
            const response = await fetch('{{ route("plumber.schedule.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'success-alert';
                alert.innerHTML = `
                    <div class="alert-content">
                        <i class="fas fa-check-circle me-2"></i>
                        ${result.message}
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                document.querySelector('.schedule-container').insertBefore(alert, document.querySelector('.schedule-header').nextSibling);
                
                // Auto-hide after 3 seconds
                setTimeout(() => alert.remove(), 3000);
            } else {
                throw new Error(result.message || 'Failed to update schedule');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating schedule: ' + error.message);
        }
    });

    // Reset defaults
    document.getElementById('resetDefaults').addEventListener('click', () => {
        if (!confirm('Reset all days to defaults and clear holidays/vacations?')) return;
        
        document.querySelectorAll('.day-schedule-card').forEach(card => {
            card.querySelector('input[value="split"]').checked = true;
            card.querySelectorAll('input[type="time"]').forEach(inp => {
                if (/\[o1\]/.test(inp.name)) inp.value = '09:00';
                if (/\[c1\]/.test(inp.name)) inp.value = '12:00';
                if (/\[o2\]/.test(inp.name)) inp.value = '13:30';
                if (/\[c2\]/.test(inp.name)) inp.value = '19:00';
                if (/\[o\](?!1|2)/.test(inp.name)) inp.value = '09:00';
                if (/\[c\](?!1|2)/.test(inp.name)) inp.value = '19:00';
            });
            applyMode(card);
        });
        
        // Clear holidays and vacations
        document.getElementById('holidayList').innerHTML = `
            <div class="holiday-item">
                <input type="date" class="holiday-input" name="holidays[]" value="">
                <button type="button" class="holiday-remove" title="Remove holiday">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.getElementById('vacList').innerHTML = `
            <div class="vacation-item">
                <div class="vacation-dates">
                    <div class="date-input-group">
                        <label class="date-label">From</label>
                        <input type="date" class="date-input" name="vacations[0][from]" value="">
                    </div>
                    <div class="date-input-group">
                        <label class="date-label">To</label>
                        <input type="date" class="date-input" name="vacations[0][to]" value="">
                    </div>
                </div>
                <div class="vacation-note">
                    <label class="note-label">Note (optional)</label>
                    <input type="text" class="note-input" name="vacations[0][note]" value="" placeholder="Vacation note">
                </div>
                <button type="button" class="vacation-remove" title="Remove vacation">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        vacationIndex = 1;
    });

    // Export JSON
    document.getElementById('exportJson').addEventListener('click', () => {
        const form = document.getElementById('scheduleForm');
        const formData = new FormData(form);
        const data = {};
        
        // Convert FormData to structured object
        for (let [key, value] of formData.entries()) {
            if (key.includes('[')) {
                const parts = key.match(/(\w+)\[([^\]]+)\](?:\[([^\]]+)\])?/);
                if (parts) {
                    const [, main, sub, subsub] = parts;
                    if (!data[main]) data[main] = {};
                    if (subsub) {
                        if (!data[main][sub]) data[main][sub] = {};
                        data[main][sub][subsub] = value;
                    } else {
                        data[main][sub] = value;
                    }
                }
            } else {
                data[key] = value;
            }
        }
        
        // Add metadata
        data.timezone = '{{ $schedule->timezone ?? "Europe/Brussels" }}';
        data.exported_at = new Date().toISOString();
        
        // Create and download file
        const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'plumber-schedule.json';
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            URL.revokeObjectURL(a.href);
            a.remove();
        }, 0);
    });
});
</script>
@endpush
