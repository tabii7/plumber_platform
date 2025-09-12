@extends('layouts.modern-dashboard')

@section('title', 'Schedule Management')

@section('page-title', 'Schedule Management')

@section('sidebar-nav')
    <x-plumber-sidebar />
@endsection

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif; background: #f5f5f5; min-height: 100vh;">
  
  <!-- Header -->
  <div style="text-align: center; margin-bottom: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <h1 style="font-size: 28px; color: #333; margin: 0 0 10px 0;">Weekly Schedule Management</h1>
    <p style="color: #666; margin: 0;">Set your working hours, holidays, and vacation periods</p>
  </div>

  <!-- Message Container -->
  <div id="message-container" style="margin-bottom: 20px;"></div>

  <!-- Main Content -->
  <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
    
    <!-- Working Hours Section -->
    <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
      <h2 style="font-size: 20px; color: #333; margin: 0 0 20px 0; border-bottom: 2px solid #eee; padding-bottom: 10px;">Working Hours</h2>

    <form id="scheduleForm">
        @csrf
        
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
                            
          <div style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9;" data-day="{{ $key }}">
            <div style="font-weight: bold; color: #333; margin-bottom: 10px; font-size: 16px;">{{ $label }}</div>
            
            <!-- Mode Buttons -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap;">
              <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button type="button" class="mode-btn {{ $mode === 'closed' ? 'active' : '' }}" data-mode="closed" style="padding: 6px 12px; border: 1px solid #ccc; background: {{ $mode === 'closed' ? '#007bff' : 'white' }}; color: {{ $mode === 'closed' ? 'white' : '#333' }}; border-radius: 4px; cursor: pointer; font-size: 14px;">Closed</button>
                <button type="button" class="mode-btn {{ $mode === 'open24' ? 'active' : '' }}" data-mode="open24" style="padding: 6px 12px; border: 1px solid #ccc; background: {{ $mode === 'open24' ? '#007bff' : 'white' }}; color: {{ $mode === 'open24' ? 'white' : '#333' }}; border-radius: 4px; cursor: pointer; font-size: 14px;">24 Hours</button>
                <button type="button" class="mode-btn {{ $mode === 'split' ? 'active' : '' }}" data-mode="split" style="padding: 6px 12px; border: 1px solid #ccc; background: {{ $mode === 'split' ? '#007bff' : 'white' }}; color: {{ $mode === 'split' ? 'white' : '#333' }}; border-radius: 4px; cursor: pointer; font-size: 14px;">Split Hours</button>
                <button type="button" class="mode-btn {{ $mode === 'fullday' ? 'active' : '' }}" data-mode="fullday" style="padding: 6px 12px; border: 1px solid #ccc; background: {{ $mode === 'fullday' ? '#007bff' : 'white' }}; color: {{ $mode === 'fullday' ? 'white' : '#333' }}; border-radius: 4px; cursor: pointer; font-size: 14px;">Full Day</button>
              </div>
              
              <!-- Status Display -->
              @php
                $statusText = '';
                $statusColor = '';
                
                switch($mode) {
                  case 'closed':
                    $statusText = 'Closed';
                    $statusColor = '#dc3545';
                    break;
                  case 'open24':
                    $statusText = '24 Hours';
                    $statusColor = '#28a745';
                    break;
                  case 'split':
                    $statusText = 'Split Hours';
                    $statusColor = '#007bff';
                    break;
                  case 'fullday':
                    $statusText = 'Full Day';
                    $statusColor = '#17a2b8';
                    break;
                  default:
                    $statusText = 'Split Hours';
                    $statusColor = '#007bff';
                }
              @endphp
              <span class="day-status-badge" data-day="{{ $key }}" style="font-size: 14px; font-weight: 500; color: {{ $statusColor }};">{{ $statusText }}</span>
            </div>

            <input type="hidden" name="schedule_data[{{ $key }}][mode]" value="{{ $mode }}">
            
            <!-- Split Hours -->
            <div class="split-inputs" style="display: {{ $mode === 'split' ? 'grid' : 'none' }}; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 10px;">
              <div>
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 4px;">Open 1</label>
                <input type="time" name="schedule_data[{{ $key }}][split][o1]" value="{{ $split['o1'] }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                                                </div>
              <div>
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 4px;">Close 1</label>
                <input type="time" name="schedule_data[{{ $key }}][split][c1]" value="{{ $split['c1'] }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                                            </div>
              <div>
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 4px;">Open 2</label>
                <input type="time" name="schedule_data[{{ $key }}][split][o2]" value="{{ $split['o2'] }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                                                </div>
              <div>
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 4px;">Close 2</label>
                <input type="time" name="schedule_data[{{ $key }}][split][c2]" value="{{ $split['c2'] }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

            <!-- Full Day -->
            <div class="full-inputs" style="display: {{ $mode === 'fullday' ? 'grid' : 'none' }}; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-top: 10px;">
              <div>
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 4px;">Open</label>
                <input type="time" name="schedule_data[{{ $key }}][full][o]" value="{{ $full['o'] }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                                        </div>
              <div>
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 4px;">Close</label>
                <input type="time" name="schedule_data[{{ $key }}][full][c]" value="{{ $full['c'] }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>
                            </div>
                        @endforeach
      </form>
            </div>

            <!-- Sidebar -->
    <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
      
      <!-- Holidays -->
      <div style="margin-bottom: 30px;">
        <h3 style="font-size: 18px; color: #333; margin: 0 0 15px 0;">Holidays</h3>
        <div id="holidays-list">
                            @if(!empty($schedule->holidays))
                                @foreach($schedule->holidays as $holiday)
              <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; padding: 8px; background: #f9f9f9; border-radius: 4px;">
                <input type="date" name="holidays[]" value="{{ $holiday }}" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <button type="button" onclick="removeHoliday(this)" style="background: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">Remove</button>
                                    </div>
                                @endforeach
                            @else
            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; padding: 8px; background: #f9f9f9; border-radius: 4px;">
              <input type="date" name="holidays[]" value="" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
              <button type="button" onclick="removeHoliday(this)" style="background: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">Remove</button>
                                </div>
                            @endif
                        </div>
        <button type="button" onclick="addHoliday()" style="background: #28a745; color: white; border: none; padding: 10px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; width: 100%; margin-top: 10px;">+ Add Holiday</button>
                </div>

      <!-- Vacations -->
                        <div>
        <h3 style="font-size: 18px; color: #333; margin: 0 0 15px 0;">Vacations</h3>
        <div id="vacations-list">
                            @if(!empty($schedule->vacations))
                                @foreach($schedule->vacations as $vacation)
              <div style="margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px;">
                <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                  <input type="date" name="vacations[{{ $loop->index }}][from]" value="{{ $vacation['from'] }}" placeholder="From" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                  <input type="date" name="vacations[{{ $loop->index }}][to]" value="{{ $vacation['to'] }}" placeholder="To" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                            </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                  <input type="text" name="vacations[{{ $loop->index }}][note]" value="{{ $vacation['note'] ?? '' }}" placeholder="Note" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                  <button type="button" onclick="removeVacation(this)" style="background: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">Remove</button>
                                            </div>
                                    </div>
                                @endforeach
                            @else
            <div style="margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px;">
              <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                <input type="date" name="vacations[0][from]" value="" placeholder="From" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <input type="date" name="vacations[0][to]" value="" placeholder="To" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                        </div>
              <div style="display: flex; gap: 8px; align-items: center;">
                <input type="text" name="vacations[0][note]" value="" placeholder="Note" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <button type="button" onclick="removeVacation(this)" style="background: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">Remove</button>
                </div>
            </div>
          @endif
        </div>
        <button type="button" onclick="addVacation()" style="background: #28a745; color: white; border: none; padding: 10px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; width: 100%; margin-top: 10px;">+ Add Vacation</button>
            </div>
        </div>
    </div>

  <!-- Save Section -->
  <div style="text-align: center; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <button type="button" onclick="saveSchedule()" style="background: #007bff; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold;">Save Schedule</button>
    <p style="margin-top: 10px; color: #666; font-size: 14px;">
      Last updated: {{ $schedule->last_updated ? $schedule->last_updated->format('Y-m-d H:i') : 'Never' }}
    </p>
  </div>

</div>

<script>
// Mode button functionality
document.querySelectorAll('.mode-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const daySchedule = this.closest('[data-day]');
    const day = daySchedule.dataset.day;
    const mode = this.dataset.mode;
    
    // Update active button
    daySchedule.querySelectorAll('.mode-btn').forEach(b => {
      b.style.background = 'white';
      b.style.color = '#333';
    });
    this.style.background = '#007bff';
    this.style.color = 'white';
    
    // Update hidden input
    daySchedule.querySelector('input[name*="[mode]"]').value = mode;
    
    // Update status badge on the right side
    updateStatusBadge(day, mode);
    
    // Show/hide time inputs
    const splitInputs = daySchedule.querySelector('.split-inputs');
    const fullInputs = daySchedule.querySelector('.full-inputs');
    
    splitInputs.style.display = 'none';
    fullInputs.style.display = 'none';
    
    if (mode === 'split') {
      splitInputs.style.display = 'grid';
    } else if (mode === 'fullday') {
      fullInputs.style.display = 'grid';
            }
        });
    });

// Function to update status badge inline with the tabs
function updateStatusBadge(day, mode) {
  const statusBadge = document.querySelector(`.day-status-badge[data-day="${day}"]`);
  if (statusBadge) {
    let statusText = '';
    let statusColor = '';
    
    switch(mode) {
      case 'closed':
        statusText = 'Closed';
        statusColor = '#dc3545';
        break;
      case 'open24':
        statusText = '24 Hours';
        statusColor = '#28a745';
        break;
      case 'split':
        statusText = 'Split Hours';
        statusColor = '#007bff';
        break;
      case 'fullday':
        statusText = 'Full Day';
        statusColor = '#17a2b8';
        break;
      default:
        statusText = 'Split Hours';
        statusColor = '#007bff';
    }
    
    statusBadge.textContent = statusText;
    statusBadge.style.color = statusColor;
  }
}

// Holiday management
function addHoliday() {
  const container = document.getElementById('holidays-list');
  const div = document.createElement('div');
  div.style.cssText = 'display: flex; gap: 8px; align-items: center; margin-bottom: 8px; padding: 8px; background: #f9f9f9; border-radius: 4px;';
  div.innerHTML = `
    <input type="date" name="holidays[]" value="" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    <button type="button" onclick="removeHoliday(this)" style="background: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">Remove</button>
  `;
  container.appendChild(div);
}

function removeHoliday(btn) {
  btn.closest('div').remove();
}

// Vacation management
function addVacation() {
  const container = document.getElementById('vacations-list');
  const index = container.children.length;
  const div = document.createElement('div');
  div.style.cssText = 'margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px;';
  div.innerHTML = `
    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
      <input type="date" name="vacations[${index}][from]" value="" placeholder="From" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
      <input type="date" name="vacations[${index}][to]" value="" placeholder="To" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
    <div style="display: flex; gap: 8px; align-items: center;">
      <input type="text" name="vacations[${index}][note]" value="" placeholder="Note" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
      <button type="button" onclick="removeVacation(this)" style="background: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">Remove</button>
            </div>
  `;
  container.appendChild(div);
}

function removeVacation(btn) {
  btn.closest('div').remove();
}

// Save schedule
function saveSchedule() {
  // Collect holidays
  const holidays = [];
  document.querySelectorAll('#holidays-list input[type="date"]').forEach(input => {
    if (input.value) holidays.push(input.value);
  });
  
  // Collect vacations
  const vacations = [];
  document.querySelectorAll('#vacations-list > div').forEach(item => {
    const from = item.querySelector('input[placeholder="From"]').value;
    const to = item.querySelector('input[placeholder="To"]').value;
    const note = item.querySelector('input[placeholder="Note"]').value;
    
    if (from && to) {
      vacations.push({ from, to, note });
    }
  });
  
  // Prepare data
  const data = {
    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    schedule_data: {},
    holidays: holidays,
    vacations: vacations
  };
  
  // Collect schedule data
  document.querySelectorAll('[data-day]').forEach(daySchedule => {
    const day = daySchedule.dataset.day;
    const mode = daySchedule.querySelector('input[name*="[mode]"]').value;
    
    data.schedule_data[day] = { mode: mode };
    
    if (mode === 'split') {
      data.schedule_data[day].split = {
        o1: daySchedule.querySelector('input[name*="[split][o1]"]').value,
        c1: daySchedule.querySelector('input[name*="[split][c1]"]').value,
        o2: daySchedule.querySelector('input[name*="[split][o2]"]').value,
        c2: daySchedule.querySelector('input[name*="[split][c2]"]').value
      };
    } else if (mode === 'fullday') {
      data.schedule_data[day].full = {
        o: daySchedule.querySelector('input[name*="[full][o]"]').value,
        c: daySchedule.querySelector('input[name*="[full][c]"]').value
      };
    }
  });
  
  // Show loading
  const saveBtn = document.querySelector('button[onclick="saveSchedule()"]');
  const originalText = saveBtn.textContent;
  saveBtn.textContent = 'Saving...';
  saveBtn.disabled = true;
  
  // Send request
  fetch('{{ route("plumber.schedule.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
      'X-CSRF-TOKEN': data._token,
      'Accept': 'application/json'
                },
                body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(result => {
            if (result.success) {
      showMessage('Schedule saved successfully!', 'success');
            } else {
      showMessage('Error saving schedule: ' + (result.message || 'Unknown error'), 'error');
            }
  })
  .catch(error => {
            console.error('Error:', error);
    showMessage('Error saving schedule: ' + error.message, 'error');
  })
  .finally(() => {
    saveBtn.textContent = originalText;
    saveBtn.disabled = false;
  });
}

// Show message
function showMessage(message, type) {
  const container = document.getElementById('message-container');
  const bgColor = type === 'success' ? '#d4edda' : '#f8d7da';
  const textColor = type === 'success' ? '#155724' : '#721c24';
  const borderColor = type === 'success' ? '#c3e6cb' : '#f5c6cb';
  
  container.innerHTML = `<div style="padding: 12px; border-radius: 4px; background: ${bgColor}; color: ${textColor}; border: 1px solid ${borderColor}; text-align: center;">${message}</div>`;
  
        setTimeout(() => {
    container.innerHTML = '';
  }, 5000);
}
</script>

@endsection