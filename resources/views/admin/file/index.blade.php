@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="{{ asset('backend/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">

    <style>
        .file-path-text {
            word-break: break-all;
        }

        .file-preview-card {
            border: 1px solid #dee2e6;
            border-radius: .25rem;
            background: #f8f9fa;
            overflow: hidden;
        }

        .file-preview-stage {
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef1f4;
            padding: .75rem;
        }

        .file-preview-card.has-preview .file-preview-stage {
            min-height: 260px;
        }

        .file-preview-image {
            max-width: 100%;
            max-height: 260px;
            object-fit: contain;
        }

        .file-preview-frame {
            width: 100%;
            height: 260px;
            border: 0;
            background: #fff;
        }

        .file-preview-empty {
            text-align: center;
            color: #6c757d;
            max-width: 420px;
        }

        .file-modal-dialog {
            max-width: 920px;
        }

        @media (max-width: 767.98px) {
            .file-preview-card.has-preview .file-preview-stage {
                min-height: 200px;
            }

            .file-preview-image {
                max-height: 200px;
            }

            .file-preview-frame {
                height: 200px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="mb-1">File Management</h1>
                        <p class="text-muted mb-0">Browse, inspect, update, and remove stored file records.</p>
                    </div>
                    <button
                        type="button"
                        class="btn btn-primary"
                        data-toggle="modal"
                        data-target="#createFileModal"
                        data-file-create="true"
                        data-active-type="{{ $activeType }}">
                        <i class="fas fa-plus mr-1"></i>New
                    </button>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if ($errors->any() && ! in_array(session('open_modal'), ['edit', 'create'], true))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>There were problems with your request.</strong>
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="card card-outline card-primary">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="file-type-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ $activeType === null ? 'active' : '' }}"
                                   href="{{ route('file.index') }}">
                                    All
                                    <span class="badge badge-light ml-1">{{ $totalFiles }}</span>
                                </a>
                            </li>
                            @foreach ($fileTypes as $fileType)
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeType === $fileType ? 'active' : '' }}"
                                       href="{{ route('file.index', ['type' => $fileType]) }}">
                                        {{ strtoupper($fileType) }}
                                        <span class="badge badge-light ml-1">{{ $typeCounts[$fileType] ?? 0 }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="fileTable" class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Path / Status</th>
                                        <th>Updated</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($files as $file)
                                        @php
                                            $openUrl = $file->resolveOpenUrl();
                                            $isOpenable = $file->hasOpenableFile();
                                        @endphp
                                        <tr>
                                            <td class="align-middle">
                                                <strong>{{ $file->file_name }}</strong>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-info">{{ strtoupper($file->file_type ?: 'n/a') }}</span>
                                            </td>
                                            <td class="align-middle">
                                                {{ $file->description ?: 'No description provided.' }}
                                            </td>
                                            <td class="align-middle">
                                                <div class="file-path-text text-sm">{{ $file->file_path }}</div>
                                                <span class="badge {{ $isOpenable ? 'badge-success' : 'badge-secondary' }} mt-1">
                                                    {{ $isOpenable ? 'Openable' : 'Metadata only' }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <span data-order="{{ optional($file->updated_at)->timestamp ?? 0 }}">
                                                    {{ optional($file->updated_at)->format('M d, Y h:i A') }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="btn-group" role="group" aria-label="File actions">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-info"
                                                        data-toggle="modal"
                                                        data-target="#viewFileModal"
                                                        data-file-view="true"
                                                        data-file-id="{{ $file->id }}"
                                                        data-file-name="{{ $file->file_name }}"
                                                        data-description="{{ $file->description }}"
                                                        data-file-type="{{ $file->file_type }}"
                                                        data-file-path="{{ $file->file_path }}"
                                                        data-created-at="{{ optional($file->created_at)->format('M d, Y h:i A') }}"
                                                        data-updated-at="{{ optional($file->updated_at)->format('M d, Y h:i A') }}"
                                                        data-open-url="{{ $openUrl }}"
                                                        data-openable="{{ $isOpenable ? '1' : '0' }}"
                                                        data-is-image="{{ $file->isImageType() ? '1' : '0' }}">
                                                        View
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-warning"
                                                        data-toggle="modal"
                                                        data-target="#editFileModal"
                                                        data-file-edit="true"
                                                        data-file-id="{{ $file->id }}"
                                                        data-action="{{ route('file.update', $file) }}"
                                                        data-file-name="{{ $file->file_name }}"
                                                        data-description="{{ $file->description }}"
                                                        data-file-type="{{ $file->file_type }}"
                                                        data-file-path="{{ $file->file_path }}"
                                                        data-active-type="{{ $activeType }}"
                                                        data-open-url="{{ $openUrl }}"
                                                        data-openable="{{ $isOpenable ? '1' : '0' }}"
                                                        data-is-image="{{ $file->isImageType() ? '1' : '0' }}">
                                                        Edit
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-danger"
                                                        data-toggle="modal"
                                                        data-target="#deleteFileModal"
                                                        data-file-delete="true"
                                                        data-file-id="{{ $file->id }}"
                                                        data-action="{{ route('file.destroy', $file) }}"
                                                        data-file-name="{{ $file->file_name }}"
                                                        data-active-type="{{ $activeType }}">
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No files found for this filter.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.modals.file-modal')
@endsection

@section('scripts')
    <script src="{{ asset('backend/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(function () {
            const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
            const fileTable = $('#fileTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
                order: [[4, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [5] },
                    { searchable: false, targets: [5] },
                ],
                language: {
                    search: 'Search files:',
                    lengthMenu: 'Show _MENU_ files',
                    info: 'Showing _START_ to _END_ of _TOTAL_ files',
                    infoEmpty: 'Showing 0 to 0 of 0 files',
                    zeroRecords: 'No matching files found',
                }
            });

            function fillText(selector, value, fallback) {
                $(selector).text(value && value.length ? value : fallback);
            }

            function isImageFileType(fileType, forceImageFlag) {
                if (forceImageFlag === '1') {
                    return true;
                }

                return imageTypes.includes((fileType || '').toLowerCase());
            }

            function clearPreview(prefix) {
                const wrapper = $('#' + prefix + 'PreviewWrapper');
                const image = $('#' + prefix + 'PreviewImage');
                const frame = $('#' + prefix + 'PreviewFrame');
                const empty = $('#' + prefix + 'PreviewEmpty');
                const objectUrl = wrapper.data('objectUrl');

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    wrapper.removeData('objectUrl');
                }

                image.attr('src', '').addClass('d-none');
                frame.attr('src', 'about:blank').addClass('d-none');
                empty.removeClass('d-none').text('No accessible file was found for this record.');
                wrapper.find('.file-preview-note').addClass('d-none');
                wrapper.removeClass('has-preview').addClass('d-none');
            }

            function renderPreview(prefix, options) {
                const wrapper = $('#' + prefix + 'PreviewWrapper');
                const image = $('#' + prefix + 'PreviewImage');
                const frame = $('#' + prefix + 'PreviewFrame');
                const empty = $('#' + prefix + 'PreviewEmpty');

                clearPreview(prefix);

                if (!options.openable || !options.url) {
                    empty.text(options.emptyMessage || 'No accessible file was found for this record.');
                    return;
                }

                if (options.objectUrl) {
                    wrapper.data('objectUrl', options.objectUrl);
                }

                wrapper.removeClass('d-none').addClass('has-preview');
                empty.addClass('d-none');

                if (isImageFileType(options.fileType, options.isImage)) {
                    image.attr('src', options.url).removeClass('d-none');
                    return;
                }

                frame.attr('src', options.url).removeClass('d-none');
            }

            function populateViewModal(button) {
                fillText('#viewFileName', button.attr('data-file-name'), 'N/A');
                fillText('#viewFileDescription', button.attr('data-description'), 'No description provided.');
                fillText('#viewFileType', (button.attr('data-file-type') || 'n/a').toUpperCase(), 'N/A');
                fillText('#viewFilePath', button.attr('data-file-path'), 'N/A');
                fillText('#viewFileCreatedAt', button.attr('data-created-at'), 'N/A');
                fillText('#viewFileUpdatedAt', button.attr('data-updated-at'), 'N/A');

                const openUrl = button.attr('data-open-url');
                const isOpenable = button.attr('data-openable') === '1';
                const openLink = $('#viewFileOpenLink');
                const missingNote = $('#viewFileUnavailable');
                const iframeNote = $('#viewPreviewIframeNote');

                if (isOpenable && openUrl) {
                    openLink.attr('href', openUrl).removeClass('d-none');
                    missingNote.addClass('d-none');
                } else {
                    openLink.addClass('d-none').attr('href', '#');
                    missingNote.removeClass('d-none');
                }

                iframeNote.addClass('d-none');

                renderPreview('view', {
                    url: openUrl,
                    openable: isOpenable,
                    fileType: button.attr('data-file-type'),
                    isImage: button.attr('data-is-image'),
                    emptyMessage: 'No accessible file was found for this record.',
                });

                if (isOpenable && openUrl && !isImageFileType(button.attr('data-file-type'), button.attr('data-is-image'))) {
                    iframeNote.removeClass('d-none');
                }
            }

            function populateEditPreview(button) {
                const openUrl = button.attr('data-open-url');
                const isOpenable = button.attr('data-openable') === '1';
                const currentLink = $('#editCurrentFileLink');
                const missingNote = $('#editPreviewUnavailable');
                const iframeNote = $('#editPreviewIframeNote');

                currentLink.addClass('d-none').attr('href', '#');
                missingNote.addClass('d-none');
                iframeNote.addClass('d-none');

                renderPreview('edit', {
                    url: openUrl,
                    openable: isOpenable,
                    fileType: button.attr('data-file-type'),
                    isImage: button.attr('data-is-image'),
                    emptyMessage: 'No accessible file was found for this record.',
                });

                if (isOpenable && openUrl) {
                    currentLink.attr('href', openUrl).removeClass('d-none');

                    if (!isImageFileType(button.attr('data-file-type'), button.attr('data-is-image'))) {
                        iframeNote.removeClass('d-none');
                    }
                } else {
                    missingNote.removeClass('d-none');
                }
            }

            function populateEditModal(button) {
                $('#editFileForm').attr('action', button.attr('data-action'));
                $('#editFileName').val(button.attr('data-file-name'));
                $('#editFileDescription').val(button.attr('data-description'));
                $('#editFileType').val((button.attr('data-file-type') || 'n/a').toUpperCase());
                $('#editFilePath').val(button.attr('data-file-path'));
                $('#editActiveType').val(button.attr('data-active-type'));
                $('#editReplacementFile').val('');
                populateEditPreview(button);
            }

            function populateCreateModal(button) {
                $('#createFileForm')[0].reset();
                $('#createActiveType').val(button.attr('data-active-type'));
                $('#createPreviewIframeNote').addClass('d-none');
                clearPreview('create');
            }

            function populateDeleteModal(button) {
                $('#deleteFileForm').attr('action', button.attr('data-action'));
                $('#deleteFileName').text(button.attr('data-file-name'));
                $('#deleteActiveType').val(button.attr('data-active-type'));
            }

            $('#editReplacementFile').on('change', function () {
                const selectedFile = this.files && this.files[0] ? this.files[0] : null;
                const currentLink = $('#editCurrentFileLink');
                const missingNote = $('#editPreviewUnavailable');
                const iframeNote = $('#editPreviewIframeNote');

                currentLink.addClass('d-none');
                missingNote.addClass('d-none');
                iframeNote.addClass('d-none');

                if (!selectedFile) {
                    const sourceButton = $('[data-file-edit][data-file-id="' + $('#editFileForm').data('fileId') + '"]').first();

                    if (sourceButton.length) {
                        populateEditPreview(sourceButton);
                    } else {
                        clearPreview('edit');
                    }

                    return;
                }

                const objectUrl = URL.createObjectURL(selectedFile);
                const fileNameParts = selectedFile.name.split('.');
                const fileType = fileNameParts.length > 1 ? fileNameParts.pop().toLowerCase() : '';

                renderPreview('edit', {
                    url: objectUrl,
                    objectUrl: objectUrl,
                    openable: true,
                    fileType: fileType,
                    isImage: selectedFile.type && selectedFile.type.indexOf('image/') === 0 ? '1' : '0',
                    emptyMessage: 'Preview is not available for the selected file.',
                });

                if (!isImageFileType(fileType, selectedFile.type && selectedFile.type.indexOf('image/') === 0 ? '1' : '0')) {
                    iframeNote.removeClass('d-none');
                }
            });

            $('#createUploadedFile').on('change', function () {
                const selectedFile = this.files && this.files[0] ? this.files[0] : null;
                const iframeNote = $('#createPreviewIframeNote');

                iframeNote.addClass('d-none');

                if (!selectedFile) {
                    clearPreview('create');
                    return;
                }

                const objectUrl = URL.createObjectURL(selectedFile);
                const fileNameParts = selectedFile.name.split('.');
                const fileType = fileNameParts.length > 1 ? fileNameParts.pop().toLowerCase() : '';
                const isImage = selectedFile.type && selectedFile.type.indexOf('image/') === 0 ? '1' : '0';

                renderPreview('create', {
                    url: objectUrl,
                    objectUrl: objectUrl,
                    openable: true,
                    fileType: fileType,
                    isImage: isImage,
                    emptyMessage: 'Preview is not available for the selected file.',
                });

                if (!isImageFileType(fileType, isImage)) {
                    iframeNote.removeClass('d-none');
                }
            });

            $('#editFileModal').on('hidden.bs.modal', function () {
                clearPreview('edit');
                $('#editReplacementFile').val('');
            });

            $('#createFileModal').on('hidden.bs.modal', function () {
                clearPreview('create');
                $('#createUploadedFile').val('');
            });

            $('#viewFileModal').on('hidden.bs.modal', function () {
                clearPreview('view');
                $('#viewPreviewIframeNote').addClass('d-none');
            });

            $(document).on('click', '[data-file-view]', function () {
                populateViewModal($(this));
            });

            $(document).on('click', '[data-file-create]', function () {
                populateCreateModal($(this));
            });

            $(document).on('click', '[data-file-edit]', function () {
                $('#editFileForm').data('fileId', $(this).attr('data-file-id'));
                populateEditModal($(this));
            });

            $(document).on('click', '[data-file-delete]', function () {
                populateDeleteModal($(this));
            });

            @if (session('open_modal') === 'edit' && session('modal_file_id'))
                const reopenButton = $('[data-file-edit][data-file-id="{{ session('modal_file_id') }}"]').first();

                if (reopenButton.length) {
                    $('#editFileForm').data('fileId', reopenButton.attr('data-file-id'));
                    $('#editFileForm').attr('action', reopenButton.attr('data-action'));
                    $('#editFileType').val((reopenButton.attr('data-file-type') || 'n/a').toUpperCase());
                    $('#editFilePath').val(reopenButton.attr('data-file-path'));
                    populateEditPreview(reopenButton);
                }

                $('#editFileModal').modal('show');
            @endif

            @if (session('open_modal') === 'create')
                $('#createFileModal').modal('show');
            @endif
        });
    </script>
@endsection
