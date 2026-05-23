<div>
    @if($isOpen)
        <div class="modal fade show d-block" id="createModal" tabindex="-1" role="dialog"
             style="background: rgba(0, 0, 0, 0.5);" aria-modal="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form class="forms-sample" wire:submit.prevent="{{ $actionMethod }}">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $modalTitle }}</h5>
                            <button type="button" wire:click="closeModal" class="btn-close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">Company Name</label>
                                <input wire:model="name" type="text" class="form-control" id="name"
                                       placeholder="Company name">
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select wire:model="status" class="form-select" id="status">
                                    <option value="{{ ACTIVE_STATUS }}">Active</option>
                                    <option value="{{INACTIVE_STATUS}}">Inactive</option>
                                </select>
                                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click="closeModal" class="btn btn-secondary">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
