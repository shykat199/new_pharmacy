<div>
    @if($isOpen)
        <div class="modal fade show d-block" id="createModal" tabindex="-1" role="dialog" style="background: rgba(0, 0, 0, 0.5);" aria-modal="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form class="forms-sample" wire:submit.prevent="{{ $actionMethod }}">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $modalTitle }}</h5>
                            <button type="button" wire:click="closeModal" class="btn-close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input wire:model="name" type="text" class="form-control" id="name" placeholder="Username">
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3" x-data x-init="$nextTick(() => {
                            const input = $el.querySelector('input');
                            input.setAttribute('name', 'custom_email_' + Date.now());
                            input.setAttribute('autocomplete', 'nope'); // fake value to confuse autofill
                        })">
                                <label for="custom_email" class="form-label">Email</label>

                                <!-- Dummy fake input to absorb autofill -->
                                <input type="text" name="fake-email" style="position: absolute; top: -9999px; left: -9999px;" autocomplete="off">

                                <input
                                    wire:model="email"
                                    type="email"
                                    class="form-control"
                                    id="custom_email"
                                    placeholder="Email"
                                    autocomplete="nope"
                                >
                                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input wire:model="phone" type="text" class="form-control" id="phone" placeholder="Phone number">
                                @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <!-- Password Field -->
                            <div x-data="{ show: false }" class="position-relative mb-3"
                                 x-init="$nextTick(() => {
                                 const input = $el.querySelector('input');
                                 input.setAttribute('name', 'custom_pass_' + Date.now());
                                 input.setAttribute('autocomplete', 'new-password');
                             })">

                                <label for="password_field" class="form-label">Password</label>

                                <input :type="show ? 'text' : 'password'"
                                       wire:model="password"
                                       class="form-control"
                                       id="password_field"
                                       placeholder="Password"
                                       autocomplete="new-password">

                                    <span @click="show = !show"
                                          class="fa fa-fw"
                                          :class="show ? 'fa-eye-slash' : 'fa-eye'"
                                          style="position: absolute; top: 70%; right: 15px; transform: translateY(-50%); cursor: pointer; padding: 6px; border-radius: 5px; font-size: 16px; color: #333;">
                                    </span>

                                @error('password')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea wire:model="address" class="form-control" id="address" rows="5"></textarea>
                                @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            @if($actionMethod !== 'saveStaff' && $actionMethod !== 'saveCustomer')
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select wire:model="status" class="form-select" id="status">
                                        <option value="{{ ACTIVE_STATUS }}">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            @endif

{{--                            @if($actionMethod === 'saveStaff' || $actionMethod == 'saveCustomer')--}}
{{--                                <div class="mb-3">--}}
{{--                                    <label for="password" class="form-label">Password</label>--}}
{{--                                    <input wire:model="password" type="password" class="form-control" id="password" placeholder="Password">--}}
{{--                                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror--}}
{{--                                </div>--}}
{{--                            @endif--}}

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
