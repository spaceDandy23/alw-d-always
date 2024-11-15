<div class="row g-3 align-items-center">
    @include('partials.alerts')
    <div class="col-auto">
        <label for="name" class="form-label">Student Name</label>
    </div>
    <div class="col">
        <input type="text" name="name" id="name" class="form-control" placeholder="Enter Student Name">
    </div>
    <div class="col-auto">
        <label for="grade" class="form-label">Grade</label>
    </div>
    <div class="col">
        <select class="form-select" id="grade" name="grade">
            <option value="">-- Select Grade --</option>
            @for($i = 7; $i <= 12; $i++)
                <option value="{{ $i }}">{{ $i }}</option>
            @endfor
        </select>
    </div>
    <div class="col-auto">
        <label for="filter_section" class="form-label">Section</label>                                              
    </div>
    <div class="col">
        <select id="filter_section" class="form-select" name="section">
            <option value="">-- Select Section --</option>
            @for($i = 1; $i <= 3; $i++)
                <option value="{{ $i }}">{{ $i }}</option>
            @endfor
        </select>  
    </div>
</div>
<div class="row g-3 mt-2 align-items-center">
    <div class="col-auto">
        <label for="start_date" class="form-label">Start Date</label>
    </div>
    <div class="col">
        <input id="start_date" type="date" name="start_date" class="form-control">
    </div>
    <div class="col-auto">
        <label for="end_date" class="form-label">End Date</label>
    </div>
    <div class="col">
        <input id="end_date" type="date" name="end_date" class="form-control">
    </div>
</div>