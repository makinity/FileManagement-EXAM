@php
    $modalFile = session('open_modal') === 'edit' ? $files->firstWhere('id', session('modal_file_id')) : null;
@endphp

<div class="modal fade" id="createFileModal" tabindex="-1" role="dialog" aria-labelledby="createFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg file-modal-dialog" role="document">
        <div class="modal-content">
            <form id="createFileForm" action="{{ route('file.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="createFileModalLabel">Create File</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    @if ($errors->any() && session('open_modal') === 'create')
                        <div class="alert alert-danger">
                            <strong>Unable to create file.</strong>
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="file-preview-card mb-4 d-none" id="createPreviewWrapper">
                        <div class="file-preview-stage">
                            <img
                                src=""
                                alt="Selected file preview"
                                class="file-preview-image d-none"
                                id="createPreviewImage">
                            <iframe
                                src="about:blank"
                                class="file-preview-frame d-none"
                                id="createPreviewFrame"></iframe>
                            <div class="file-preview-empty" id="createPreviewEmpty">
                                Select a file to preview it before saving.
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <span class="text-muted d-none file-preview-note" id="createPreviewIframeNote">
                                Browser preview depends on file-type support. If the iframe stays blank, the file can still be uploaded.
                            </span>
                        </div>
                    </div>

                    <input type="hidden" name="active_type" id="createActiveType" value="{{ old('active_type', $activeType) }}">

                    <div class="form-group">
                        <label for="createFileName">File Name</label>
                        <input type="text"
                               class="form-control @error('file_name') is-invalid @enderror"
                               id="createFileName"
                               name="file_name"
                               value="{{ old('file_name') }}"
                               required>
                        @error('file_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="createFileDescription">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="createFileDescription"
                                  name="description"
                                  rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <label for="createUploadedFile">Upload File</label>
                        <input type="file"
                               class="form-control-file @error('uploaded_file') is-invalid @enderror"
                               id="createUploadedFile"
                               name="uploaded_file"
                               required>
                        @error('uploaded_file')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="viewFileModal" tabindex="-1" role="dialog" aria-labelledby="viewFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg file-modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="viewFileModalLabel">File Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="file-preview-card mb-4 d-none" id="viewPreviewWrapper">
                    <div class="file-preview-stage">
                        <img src="" alt="File preview" class="file-preview-image d-none" id="viewPreviewImage">
                        <iframe src="about:blank" class="file-preview-frame d-none" id="viewPreviewFrame"></iframe>
                        <div class="file-preview-empty" id="viewPreviewEmpty">
                            No accessible file was found for this record.
                        </div>
                    </div>
                    <div class="card-footer bg-white d-none file-preview-note" id="viewPreviewIframeNote">
                        Browser preview depends on file-type support. If the iframe stays blank, use the Open File button.
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted mb-1">File Name</label>
                        <p class="mb-0" id="viewFileName">N/A</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted mb-1">File Type</label>
                        <p class="mb-0" id="viewFileType">N/A</p>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="font-weight-bold text-muted mb-1">Description</label>
                        <p class="mb-0" id="viewFileDescription">No description provided.</p>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="font-weight-bold text-muted mb-1">Stored Path</label>
                        <p class="mb-0 file-path-text" id="viewFilePath">N/A</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted mb-1">Created At</label>
                        <p class="mb-0" id="viewFileCreatedAt">N/A</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted mb-1">Updated At</label>
                        <p class="mb-0" id="viewFileUpdatedAt">N/A</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <span class="text-muted d-none" id="viewFileUnavailable">No accessible file was found for this record.</span>
                <a href="#" class="btn btn-info d-none" id="viewFileOpenLink" target="_blank" rel="noopener noreferrer">Open File</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editFileModal" tabindex="-1" role="dialog" aria-labelledby="editFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg file-modal-dialog" role="document">
        <div class="modal-content">
            <form id="editFileForm"
                  action="{{ $modalFile ? route('file.update', $modalFile) : '#' }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="editFileModalLabel">Edit File</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    @if ($errors->any() && session('open_modal') === 'edit')
                        <div class="alert alert-danger">
                            <strong>Unable to save changes.</strong>
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="file-preview-card mb-4 d-none" id="editPreviewWrapper">
                        <div class="file-preview-stage">
                            <img
                                src=""
                                alt="Current file preview"
                                class="file-preview-image d-none"
                                id="editPreviewImage">
                            <iframe
                                src="about:blank"
                                class="file-preview-frame d-none"
                                id="editPreviewFrame"></iframe>
                            <div class="file-preview-empty" id="editPreviewEmpty">
                                No accessible file was found for this record.
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <span class="text-muted d-none" id="editPreviewUnavailable">No accessible file was found for this record.</span>
                            <span class="text-muted d-none file-preview-note" id="editPreviewIframeNote">
                                Browser preview depends on file-type support. If the iframe stays blank, open the file directly.
                            </span>
                            <a href="#" class="btn btn-sm btn-outline-primary float-right d-none" id="editCurrentFileLink" target="_blank" rel="noopener noreferrer">
                                Open Current File
                            </a>
                        </div>
                    </div>

                    <input type="hidden" name="active_type" id="editActiveType" value="{{ old('active_type', $activeType) }}">

                    <div class="form-group">
                        <label for="editFileName">File Name</label>
                        <input type="text"
                               class="form-control @error('file_name') is-invalid @enderror"
                               id="editFileName"
                               name="file_name"
                               value="{{ old('file_name', $modalFile?->file_name) }}"
                               required>
                        @error('file_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="editFileDescription">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="editFileDescription"
                                  name="description"
                                  rows="4">{{ old('description', $modalFile?->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editFileType">Current File Type</label>
                                <input type="text"
                                       class="form-control"
                                       id="editFileType"
                                       value="{{ strtoupper($modalFile?->file_type ?? 'n/a') }}"
                                       readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editReplacementFile">Replace File</label>
                                <input type="file"
                                       class="form-control-file @error('replacement_file') is-invalid @enderror"
                                       id="editReplacementFile"
                                       name="replacement_file">
                                @error('replacement_file')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="editFilePath">Current Path</label>
                        <input type="text"
                               class="form-control"
                               id="editFilePath"
                               value="{{ $modalFile?->file_path }}"
                               readonly>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteFileModal" tabindex="-1" role="dialog" aria-labelledby="deleteFileModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="deleteFileForm" action="#" method="POST">
                @csrf
                @method('DELETE')

                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="deleteFileModalLabel">Delete File</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="active_type" id="deleteActiveType" value="{{ $activeType }}">
                    <p class="mb-0">
                        You are about to delete <strong id="deleteFileName">this file</strong>. This action cannot be undone.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete File</button>
                </div>
            </form>
        </div>
    </div>
</div>
