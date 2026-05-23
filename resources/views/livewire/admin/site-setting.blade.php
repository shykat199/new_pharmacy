<div>
    @push('style')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
            integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
    @endpush
    @section('admin.breadcrumb')
        <li class="breadcrumb-item" aria-current="page">{{ $page }}</li>
    @endsection
    <div class="row">
        <form class="forms-sample" wire:submit.prevent="saveSetting" enctype="multipart/form-data">
            <div class="row">
                <div class="col-12 col-md-6 ">
                    <div class="mb-3">
                        <label for="shopName" class="form-label">Shop Name</label>
                        <input type="text" wire:model="shopName" class="form-control" id="shopName"
                            autocomplete="off" placeholder="Shop Name">
                    </div>
                </div>
                <div class="col-12 col-md-6 ">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" wire:model="email" class="form-control" id="email" autocomplete="off"
                            placeholder="Username">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" wire:model="phone" class="form-control" id="phone" autocomplete="off"
                            placeholder="Shop Name">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" wire:model="address" class="form-control" id="address"
                            autocomplete="off" placeholder="Username">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="mb-3" x-data="{ localPreview: '' }">
                        <label class="form-label">Site Logo</label>
                        <div>
                            <img id="profileImage1"
                                :src="localPreview ? localPreview :
                                    '{{ $existing_logo ? asset('storage/' . $existing_logo) : asset('assets/images/no-image.png') }}'"
                                class="img-responsive mt-2" width="318" height="280">
                            <div class="mt-2">
                                <label class="btn btn-primary">
                                    <i class="fa fa-upload"></i> Upload
                                    <input type="file" id="imageUpload1" wire:model="site_logo"
                                        @change="localPreview = URL.createObjectURL($event.target.files[0])"
                                        style="display: none;" accept="image/*">
                                </label>
                                @error('site_logo')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="mb-3" x-data="{ localPreview: '' }">
                        <label class="form-label">Site Favicon</label>
                        <div>
                            <img id="profileImage2"
                                :src="localPreview ? localPreview :
                                    '{{ $existing_favicon ? asset('storage/' . $existing_favicon) : asset('assets/images/no-image.png') }}'"
                                class="img-responsive mt-2" width="318" height="280">
                            <div class="mt-2">
                                <label class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Upload
                                    <input type="file" id="imageUpload2" wire:model="site_favicon"
                                        @change="localPreview = URL.createObjectURL($event.target.files[0])"
                                        style="display: none;" accept="image/*">
                                </label>
                                @error('site_favicon')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary me-2">Submit</button>

        </form>
    </div>
</div>
