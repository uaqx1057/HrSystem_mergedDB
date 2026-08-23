@php($job = $job ?? null)
<div class="form-group">
    <label>Title *</label>
    <input type="text" name="title" class="form-control height-35 f-14" value="{{ old('title', $job->title ?? '') }}" required>
</div>
<div class="form-row">
    <div class="form-group col">
        <label>Department</label>
        <select name="department_id" class="form-control height-35 f-14">
            <option value="">-</option>
            @foreach($departments as $d)
                <option value="{{ $d->id }}" @selected(old('department_id', $job->department_id ?? '') == $d->id)>{{ $d->team_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col">
        <label>Designation</label>
        <select name="designation_id" class="form-control height-35 f-14">
            <option value="">-</option>
            @foreach($designations as $d)
                <option value="{{ $d->id }}" @selected(old('designation_id', $job->designation_id ?? '') == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col">
        <label>Branch</label>
        <select name="branch_id" class="form-control height-35 f-14">
            <option value="">-</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}" @selected(old('branch_id', $job->branch_id ?? '') == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group col">
        <label>Employment type</label>
        <input type="text" name="employment_type" class="form-control height-35 f-14" value="{{ old('employment_type', $job->employment_type ?? '') }}">
    </div>
    <div class="form-group col">
        <label>Positions</label>
        <input type="number" min="1" name="positions_count" class="form-control height-35 f-14" value="{{ old('positions_count', $job->positions_count ?? 1) }}">
    </div>
    <div class="form-group col">
        <label>Closes at</label>
        <input type="date" name="closes_at" class="form-control height-35 f-14" value="{{ old('closes_at', $job->closes_at ?? '') }}">
    </div>
</div>
<div class="form-group">
    <label>Description</label>
    <div id="description-editor" class="border rounded" style="min-height: 160px;">{!! old('description', $job->description ?? '') !!}</div>
    <textarea name="description" id="description-text" class="d-none">{{ old('description', $job->description ?? '') }}</textarea>
</div>
<div class="form-group">
    <label>Requirements</label>
    <div id="requirements-editor" class="border rounded" style="min-height: 160px;">{!! old('requirements', $job->requirements ?? '') !!}</div>
    <textarea name="requirements" id="requirements-text" class="d-none">{{ old('requirements', $job->requirements ?? '') }}</textarea>
</div>
