<div class="media align-items-center mw-250">
    <a href="{{ route('drivers.show', [$id]) }}"
        class="position-relative disabled-link">
        <img src="{{ $image_url }}" class="mr-2 taskEmployeeImg rounded-circle" alt="{{ $name }}">
    </a>
    <div class="media-body {{$status}}">
        <h5 class="mb-0 f-12">
            <a href="{{ route('drivers.show', [$id]) }}"
                class="text-darkest-grey disabled-link">{{ $name }}</a>
        </h5>
    </div>
</div>
