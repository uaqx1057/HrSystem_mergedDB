@php
    $editDriverPermission = user()->permission('edit_receipt_voucher');
    $deleteDriverPermission = user()->permission('delete_receipt_voucher');
@endphp

<div class="task_view">

    <div class="dropdown">
        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
            id="dropdownMenuLink-{{ $id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-options-vertical icons"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-{{ $id }}"
            tabindex="0">

            <a href="{{ route('receipt-voucher.show', [$id]) }}" class="dropdown-item"><i
                    class="fa fa-eye mr-2"></i>{{ __('app.view') }}</a>
            <a href="{{ route('receipt-voucher.download', [$id]) }}" class="dropdown-item"><i
                    class="fa fa-eye mr-2"></i>Download</a>
            <a href="{{ route('receipt-voucher.download', ['id' => $id, 'view' => 'true']) }}" class="dropdown-item"><i
                    class="fa fa-eye mr-2"></i>View PDF</a>

            @if ($editDriverPermission == 'all')
            <a class="dropdown-item openRightModal" href="{{ route('receipt-voucher.edit', [$id]) }}">
                <i class="fa fa-edit mr-2"></i>
                {{ trans('app.edit') }}
            </a>
            @endif

            @if ($deleteDriverPermission == 'all')
            {{-- <a class="dropdown-item delete-table-row" href="javascript:;" data-driver-id="{{ $id }}">
                <i class="fa fa-trash mr-2"></i>
                {{ trans('app.delete') }}
            </a> --}}
            @endif
        </div>
    </div>
</div>
