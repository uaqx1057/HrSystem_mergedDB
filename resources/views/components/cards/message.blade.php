@php
    $isSentByMe = user()->id == $user->id;
@endphp

<div class="d-flex {{ $isSentByMe ? 'justify-content-end' : 'justify-content-start' }} message-row mb-2"
     id="message-{{ $message->id }}">

    <div class="message-bubble-wrapper {{ $isSentByMe ? 'sent' : 'received' }}">

        <div class="message-bubble">

            @if ($isSentByMe || in_array('admin', user_roles()))
                <div class="dropdown message-action">
                    <button class="btn btn-sm p-0 dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-h"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                         aria-labelledby="dropdownMenuLink" tabindex="0">
                        <a class="dropdown-item delete-message"
                           data-row-id="{{ $message->id }}" data-user-id="{{ $user->id }}"
                           href="javascript:;">@lang('app.delete')</a>
                    </div>
                </div>
            @endif

            @if ($message->message != '')
                <div class="message-text text-break">
                    <span>{!! nl2br($message->message) !!}</span>
                </div>
            @endif

            {{ $slot }}

            @if ($message->files->count())
                <div class="d-flex flex-wrap message-files">
                    @foreach ($message->files as $file)
                        <x-file-card :fileName="$file->filename" :dateAdded="$file->created_at->diffForHumans()">
                            @if ($file->icon == 'images')
                                <img src="{{ $file->file_url }}">
                            @else
                                <i class="fa {{ $file->icon }} text-lightest"></i>
                            @endif

                            <x-slot name="action">
                                <div class="dropdown ml-auto file-action">
                                    <button class="btn btn-lg f-14 p-0 text-lightest text-capitalize rounded dropdown-toggle"
                                            type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                         aria-labelledby="dropdownMenuLink" tabindex="0">
                                        <a class="dropdown-item" target="_blank"
                                           href="{{ $file->file_url }}">@lang('app.view')</a>
                                        <a class="dropdown-item"
                                           href="{{ route('message_file.download', md5($file->id)) }}">@lang('app.download')</a>
                                        @if (user()->id == $user->id)
                                            <a class="dropdown-item delete-file"
                                               data-row-id="{{ $file->id }}"
                                               href="javascript:;">@lang('app.delete')</a>
                                        @endif
                                    </div>
                                </div>
                            </x-slot>
                        </x-file-card>
                    @endforeach
                </div>
            @endif

            <div class="message-time">
                {{ $message->created_at->timezone(company()->timezone)->translatedFormat(company()->time_format) }}
            </div>
        </div>
    </div>
</div><!-- message-row end -->
