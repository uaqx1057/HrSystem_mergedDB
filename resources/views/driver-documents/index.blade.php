@extends('layouts.app')

@section('content')
    <div class="content-wrapper">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <x-forms.link-primary :link="route('driver-documents.create')" class="mr-3" icon="plus">
                Add Driver Document
            </x-forms.link-primary>

        </div>

        <div class="card">
            <div class="card-header form-heading-background d-flex justify-content-between align-items-center">
                <span>Driver Documents</span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Driver</th>
                            <th>Doucment Type</th>
                            {{-- <th>file Path</th> --}}
                            <th>Original Name</th>
                            {{-- <th>File Size</th>
                            <th>Uploaded From</th>
                            <th>Uploaded By</th> --}}
                            <th>Expire At</th>
                            <th>Note</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td>{{ $document->id }}</td>
                                <td>{{ $document->driver->name }}</td>
                                <td>{{ $document->document_type }}</td>
                                <td>
                                    <a href="{{ route('driver-documents.preview', $document->id) }}" target="_blank"
                                        rel="noopener noreferrer">
                                        {{ $document->original_name }}
                                    </a>
                                </td>
                                <td>
                                    {{ $document->expires_at ? \Carbon\Carbon::parse($document->expires_at)->format('d-m-Y') : '-' }}
                                </td>
                                <td>{{ $document->notes }}</td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center">
                                        {{-- <a href="#">
                                            <i class="fa fa-eye mr-2"></i>
                                        </a> --}}
                                        <a href="{{ route('driver-documents.edit', [$document->id]) }}"
                                            style="color: green !important">
                                            <i class="fa fa-edit mr-2"></i>
                                        </a>
                                        {{-- <a href="{{ route('driver-documents.destroy', [$document->id]) }}" class="" style="color: rgb(184, 11, 11) !important">
                                            <i class="fa fa-trash mr-2"></i>
                                        </a> --}}

                                        <form method="POST" action="{{ route('driver-documents.destroy', [$document->id]) }}" onsubmit="return confirm('Delete this document?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="" style="color: rgb(184, 11, 11) !important; background: transparent !important;"><i class="fa fa-trash mr-2"></i></button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">No Document Found found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $documents->links() }}
            </div>
        </div>
    </div>
@endsection
