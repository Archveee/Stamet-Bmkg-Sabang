<div class="p-2 font-mono font-semibold text-gray-100 leading-tight text-xs sm:text-sm drop-shadow-[0_1px_1px_rgba(0,0,0,0.8)]" wire:poll.30s>
    <div class="grid grid-cols-[max-content_10px_auto] gap-x-1 gap-y-px">
        <div>UPDATE</div><div>:</div><div>{{ $timestamp ?? '-' }}</div>
        <div>DD</div><div>:</div><div>{{ isset($wind_dir_deg_dd) ? $wind_dir_deg_dd . '°' : '-' }}</div>

        <div>FF</div><div>:</div>
        <div>
            @if(isset($wind_speed_ff))
                {{ $wind_speed_ff }} kt ({{ round($wind_speed_ff * 1.852) }} km/jam)
            @else
                -
            @endif
        </div>

        <div>FFx</div><div>:</div>
        <div>
            @if(isset($wind_speed_ff_maks))
                {{ $wind_speed_ff_maks }} kt ({{ round($wind_speed_ff_maks * 1.852) }} km/jam)
            @else
                -
            @endif
        </div>

        <div>TT</div><div>:</div><div>{{ isset($temp_dry) ? $temp_dry . '°C' : '-' }}</div>
        <div>TD</div><div>:</div><div>{{ isset($temp_dew) ? $temp_dew . '°C' : '-' }}</div>
        <div>RH</div><div>:</div><div>{{ isset($humidity) ? $humidity . '%' : '-' }}</div>
        <div>QFE</div><div>:</div><div>{{ isset($qfe) ? $qfe . ' mb' : '-' }}</div>
        <div>QNH</div><div>:</div><div>{{ isset($qnh) ? $qnh . ' mb' : '-' }}</div>
        <div>RRR</div><div>:</div><div>{{ $rain ?? '-' }}</div>
        <div>RRR1h</div><div>:</div><div>{{ $rain_last_hour ?? '-' }}</div>
    </div>
</div>
