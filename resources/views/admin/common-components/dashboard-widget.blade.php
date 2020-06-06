 <div class="col-xl-3 col-md-6">
    <div class="card mini-stat bg-primary">
        <div class="card-body mini-stat-img">
            <div class="mini-stat-icon">
                <i class="{{ $icons }}"></i>
            </div>
            <div class="text-white">
                <h6 class="text-uppercase mb-3 font-size-16">{{ $title }}</h6>
                <h2 class="mb-4">{{ $count }}</h2>
                <span class="badge {{ $badgeClass }}"> {{ $per }} </span> <span class="ml-2">
                    @if(trim($narration) == trim($per))
                    From previous month
                    @else
                    {{ \Acelle\Model\Setting::get("site_name") }} {{ $per }}
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>

                        