<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataSinoptikResource\Pages;
use App\Helpers\DateHelper;
use App\Helpers\SinoptikDataHelper;
use App\Jobs\FetchSinoptikDataJob;
use App\Models\DataSinoptik;
use App\Services\BmkgsatuApiService;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DataSinoptikResource extends Resource
{
    protected static ?string $model = DataSinoptik::class;

    protected static ?string $slug = 'data-sinoptik';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->columns(4)
                    ->schema([
                        Forms\Components\DateTimePicker::make('data_timestamp')
                            ->label('Jam Pengamatan')
                            ->suffix('UTC')
                            ->native(false)
                            ->seconds(false)
                            ->columnSpanFull()
                            ->visibleOn('create')
                            ->disabledOn('edit')
                            ->default(DateHelper::getNearestSentHour())
                            ->displayFormat('Y-m-d H:00'),

                        Forms\Components\Section::make('Angin - Penglihatan - Keadaan Cuaca')
                            ->collapsible()
                            ->columns(4)
                            ->schema([

                                Forms\Components\TextInput::make('wind_indicator_iw')
                                    ->label('Pengenal Data Angin')
                                    ->integer(),
                                Forms\Components\TextInput::make('wind_dir_deg_dd')
                                    ->label('Arah Angin (derajat)')
                                    ->integer(),
                                Forms\Components\TextInput::make('wind_speed_ff')
                                    ->label('Kecepatan Angin (Knot)')
                                    ->integer(),
                                Forms\Components\TextInput::make('visibility_vv')
                                    ->label('Penglihatan Mendatar (km)')
                                    ->integer(),
                                Forms\Components\TextInput::make('weather_indicator_ix')
                                    ->label('Pengenal Data Cuaca (ix)')
                                    ->integer(),
                                Forms\Components\Select::make('present_weather_ww')
                                    ->label('Cuaca Saat Pengamatan')
                                    ->searchable()
                                    ->options(function () {
                                        $weathers = setting('reference_ww');
                                        return array_flip($weathers);
                                    }),
                                Forms\Components\Select::make('past_weather_w1')
                                    ->label('Cuaca Saat Yang Lalu 1')
                                    ->searchable()
                                    ->options(function () {
                                        $weathers = setting('reference_w1w2');
                                        return array_flip($weathers);
                                    }),
                                Forms\Components\Select::make('past_weather_w2')
                                    ->label('Cuaca Saat Yang Lalu 2')
                                    ->searchable()
                                    ->options(function () {
                                        $weathers = setting('reference_w1w2');
                                        return array_flip($weathers);
                                    }),
                            ]),

                        Forms\Components\Section::make('Tekanan Udara')
                            ->collapsible()
                            ->columns(4)
                            ->schema([

                                Forms\Components\TextInput::make('pressure_temp_c')
                                    ->label('Derajat Panas (oC)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('pressure_reading_mb')
                                    ->label('Tekanan Dibaca (mb)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('koreksi_pressure_qff_mb_derived')
                                    ->label('Koreksi QFF (mb)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('koreksi_pressure_qfe_mb_derived')
                                    ->label('Koreksi QFE (mb)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('pressure_qff_mb_derived')
                                    ->label('QFF (mb)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('pressure_qfe_mb_derived')
                                    ->label('QFE (mb)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('pressure_tend_3h_mb_ppp')
                                    ->label('Perubahan Tekanan 3 Jam')
                                    ->numeric(),
                                Forms\Components\TextInput::make('pressure_tend_24h_mb_ppp')
                                    ->label('Perubahan Tekanan 24 Jam')
                                    ->numeric(),
                            ]),

                        Forms\Components\Section::make('Suhu Udara')
                            ->collapsible()
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('temp_drybulb_c_tttttt')
                                    ->label('Suhu Bola Kering (oC)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('temp_wetbulb_c')
                                    ->label('Suhu Bola Basah (oC)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('temp_dewpoint_c_tdtdtd')
                                    ->label('Titik Embun (oC)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('relative_humidity_pc')
                                    ->label('Lembab Nisbi RH (%)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('temp_max_c_txtxtx')
                                    ->label('Suhu Maksimum (oC)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('temp_min_c_tntntn')
                                    ->label('Suhu Minimum (oC)')
                                    ->numeric(),
                            ]),

                        Forms\Components\Section::make('Endapan')
                            ->collapsible()
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('rainfall_indicator_ir')
                                    ->label('Pengenal Data Hujan (iR)')
                                    ->integer(),
                                Forms\Components\TextInput::make('rainfall_last_mm')
                                    ->label('Hujan Sejak Takaran Terakhir (mm)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('rainfall_6h_rrr')
                                    ->label('Hujan Sejak 6 Jam Yang Lalu (mm)')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('rainfall_24h_rrrr')
                                    ->label('Hujan Sejak 24 Jam Yang Lalu (mm)')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Section::make('Perawanan')
                            ->collapsible()
                            ->columns(3)
                            ->schema([
                                Forms\Components\Section::make('')
                                    ->heading('')
                                    ->columns(3)
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\TextInput::make('cloud_cover_oktas_m')
                                            ->label('Bagian Langit Yang Tertutup Awan (okta)')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_vertical_vis')
                                            ->label('Jarak Penglihatan Vertikal (m)')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_low_type_cl')
                                            ->label('Jenis Awan Rendah')
                                            ->columnStart(1),
                                        Forms\Components\TextInput::make('cloud_med_type_cm')
                                            ->label('Jenis Awan Menengah'),
                                        Forms\Components\TextInput::make('cloud_high_type_ch')
                                            ->label('Jenis Awan Tinggi'),
                                        Forms\Components\TextInput::make('cloud_low_cover_oktas')
                                            ->label('Banyaknya Awan Rendah (okta)'),
                                        Forms\Components\TextInput::make('cloud_med_cover_oktas')
                                            ->label('Banyaknya Awan Menengah (okta)'),
                                        Forms\Components\TextInput::make('cloud_high_cover_oktas')
                                            ->label('Banyaknya Awan Tinggi (okta)'),
                                    ]),

                                Forms\Components\Section::make('Awan Rendah')
                                    ->collapsible()
                                    ->columns(7)
                                    ->schema([
                                        Forms\Components\TextInput::make('cloud_low_type_1')
                                            ->label('Jenis 1'),
                                        Forms\Components\TextInput::make('cloud_low_base_1')
                                            ->label('Tinggi Dasar')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_low_peak_1')
                                            ->label('Tinggi Puncak')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_low_cover_1')
                                            ->label('Okta')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_low_dir_1')
                                            ->label('Arah')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_elevation_angle_ec_1')
                                            ->label('Sudut Elevasi')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_low_dir_true_da_1')
                                            ->label('True dir')
                                            ->integer(),

                                        Forms\Components\TextInput::make('cloud_low_type_2')
                                            ->label('Jenis 2'),
                                        Forms\Components\TextInput::make('cloud_low_base_2')
                                            ->label('Tinggi Dasar')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_low_peak_2')
                                            ->label('Tinggi Puncak')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_low_cover_2')
                                            ->label('Okta')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_low_dir_2')
                                            ->label('Arah')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_elevation_angle_ec_2')
                                            ->label('Sudut Elevasi')
                                            ->integer(),

                                        Forms\Components\TextInput::make('cloud_low_dir_true_da_2')
                                            ->label('True dir')
                                            ->integer(),

                                        Forms\Components\TextInput::make('cloud_low_type_3')
                                            ->label('Jenis 3'),
                                        Forms\Components\TextInput::make('cloud_low_base_3')
                                            ->label('Tinggi Dasar')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_low_peak_3')
                                            ->label('Tinggi Puncak')
                                            ->placeholder('-')
                                            ->disabled(),

                                        Forms\Components\TextInput::make('cloud_low_cover_3')
                                            ->label('Okta')
                                            ->integer(),

                                        Forms\Components\TextInput::make('cloud_low_dir_3')
                                            ->label('Arah')
                                            ->integer(),

                                    ]),
                                Forms\Components\Section::make('Awan Menengah')
                                    ->collapsible()
                                    ->columns(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('cloud_med_type_1')
                                            ->label('Jenis 1'),
                                        Forms\Components\TextInput::make('cloud_med_base_1')
                                            ->label('Tinggi Dasar')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_med_cover_1')
                                            ->label('Okta')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_med_dir_1')
                                            ->label('Arah')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_med_type_2')
                                            ->label('Jenis 2'),
                                        Forms\Components\TextInput::make('cloud_med_base_2')
                                            ->label('Tinggi Dasar')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_med_cover_2')
                                            ->label('Okta')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_med_dir_2')
                                            ->label('Arah')
                                            ->integer(),
                                    ]),
                                Forms\Components\Section::make('Awan Tinggi')
                                    ->collapsible()
                                    ->columns(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('cloud_high_type_1')
                                            ->label('Jenis 1'),
                                        Forms\Components\TextInput::make('cloud_high_base_1')
                                            ->label('Tinggi Dasar')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_high_cover_1')
                                            ->label('Okta')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_high_dir_1')
                                            ->label('Arah')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_high_type_2')
                                            ->label('Jenis 2'),
                                        Forms\Components\TextInput::make('cloud_high_base_2')
                                            ->label('Tinggi Dasar')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_high_cover_2')
                                            ->label('Okta')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_high_dir_2')
                                            ->label('Arah')
                                            ->integer(),
                                    ]),

                                Forms\Components\Section::make('Lapisan Awan')
                                    ->collapsible()
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('cloud_layer_1_type_c')
                                            ->label('Jenis 1'),
                                        Forms\Components\TextInput::make('cloud_layer_1_height_m_hshs')
                                            ->label('Tinggi (m)')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_layer_1_amt_oktas_ns')
                                            ->label('Jumlah (okta)')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_layer_2_type_c')
                                            ->label('Jenis 2'),
                                        Forms\Components\TextInput::make('cloud_layer_2_height_m_hshs')
                                            ->label('Tinggi (m)')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_layer_2_amt_oktas_ns')
                                            ->label('Jumlah (okta)')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_layer_3_type_c')
                                            ->label('Jenis 3'),
                                        Forms\Components\TextInput::make('cloud_layer_3_height_m_hshs')
                                            ->label('Tinggi (m)')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_layer_3_amt_oktas_ns')
                                            ->label('Jumlah (okta)')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_layer_4_type_c')
                                            ->label('Jenis 4'),
                                        Forms\Components\TextInput::make('cloud_layer_4_height_m_hshs')
                                            ->label('Tinggi (m)')
                                            ->integer(),
                                        Forms\Components\TextInput::make('cloud_layer_4_amt_oktas_ns')
                                            ->label('Jumlah (okta)')
                                            ->integer(),

                                    ]),

                            ]),

                        Forms\Components\Section::make('Penyinaran Matahari')
                            ->collapsible()
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('evaporation_eq_indicator_ie')
                                    ->label('Pengenal Data Alat Penguapan (iE)')
                                    ->integer(),
                                Forms\Components\TextInput::make('evaporation_24hours_mm_eee')
                                    ->label('Jumlah penguapan selama 24 jam yang lalu (mm)')
                                    ->numeric(),

                                Forms\Components\TextInput::make('solar_rad_24h_jcm2_f24')
                                    ->label('Radiasi Total Selama 24 jam yang lalu (Joule/cm2)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('sunshine_h_sss')
                                    ->label('Penyinaran Matahari (jam)')
                                    ->numeric(),
                            ]),

                        Forms\Components\Section::make('Tanah/Catatan')
                            ->collapsible()
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('land_cond')
                                    ->label('Keadaan Tanah')
                                    ->integer(),
                                Forms\Components\TextInput::make('land_note')
                                    ->label('Catatan')
                                    ->maxLength(255),

                            ]),

                        Forms\Components\Section::make('Observer')
                            ->collapsible()
                            ->schema([
                                Forms\Components\Select::make('observer_id')
                                    ->label('Nama Terang')
                                    ->options(function () {
                                        $observersData = setting('bmkgsatu_observer_list');
                                        $observers = [];
                                        foreach ($observersData as $observer) {
                                            $observers[$observer['observer_id']] = $observer['observer_name'];
                                        }

                                        return $observers;
                                    }),
                            ]),

                        Forms\Components\Section::make('Sandi Synop')
                            ->collapsible()
                            ->schema([
                                Forms\Components\Textarea::make('encoded_synop')
                                    ->label('')
                                    ->rows(4)
                                    ->readOnly(),
                            ]),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([6, 12, 24, 36, 48, 60, 72])
            ->defaultSort('data_timestamp', 'desc')
            ->poll('60s')
            ->searchOnBlur(true)
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('data_timestamp')
                    ->label('Jam Pengamatan')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('cloud_cover_oktas_m')
                    ->label('N')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('wind_indicator_iw')
                    ->label('iw')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('wind_dir_deg_dd')
                    ->label('dd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('wind_speed_ff')
                    ->label('ff')
                    ->sortable(),
                Tables\Columns\TextColumn::make('visibility_vv')
                    ->label('VV')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weather_indicator_ix')
                    ->label('ix')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('present_weather_ww')
                    ->label('ww')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn($state) => SinoptikDataHelper::getWeatherNameString($state, true)),
                Tables\Columns\TextColumn::make('past_weather_w1')
                    ->label('W1')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn($state) => SinoptikDataHelper::getWeatherNameString($state)),

                Tables\Columns\TextColumn::make('past_weather_w2')
                    ->label('W2')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn($state) => SinoptikDataHelper::getWeatherNameString($state)),

                Tables\Columns\TextColumn::make('cloud_low_cover_oktas')
                    ->label('Nh')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('koreksi_pressure_qff_mb_derived')
                    ->label('Koreksi QFF')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('koreksi_pressure_qfe_mb_derived')
                    ->label('Koreksi QFE')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('temp_drybulb_c_tttttt')
                    ->label('Tt')
                    ->sortable(),
                Tables\Columns\TextColumn::make('temp_wetbulb_c')
                    ->label('Tw')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('temp_dewpoint_c_tdtdtd')
                    ->label('Td')
                    ->sortable(),
                Tables\Columns\TextColumn::make('relative_humidity_pc')
                    ->label('RH')
                    ->sortable(),
                Tables\Columns\TextColumn::make('temp_max_c_txtxtx')
                    ->label('TxTxTx')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('temp_min_c_tntntn')
                    ->label('Tn')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pressure_qff_mb_derived')
                    ->label('QFF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pressure_qfe_mb_derived')
                    ->label('QFE')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pressure_temp_c')
                    ->label('Derajat Panas')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('pressure_reading_mb')
                    ->label('Tekanan Dibaca')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('pressure_tend_3h_mb_ppp')
                    ->label('ppp')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('pressure_tend_24h_mb_ppp')
                    ->label('p24p24p24')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('rainfall_indicator_ir')
                    ->label('iR')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('rainfall_last_mm')
                    ->label('RRR')
                    ->wrapHeader()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rainfall_6h_rrr')
                    ->label('RRR6')
                    ->wrapHeader()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('rainfall_24h_rrrr')
                    ->label('RRR24')
                    ->wrapHeader()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('cloud_low_type_cl')
                    ->label('Cl')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_med_type_cm')
                    ->label('Cm')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_high_type_ch')
                    ->label('Ch')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_med_cover_oktas')
                    ->label('Banyaknya Awan Menengah')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_high_cover_oktas')
                    ->label('Banyaknya Awan Tinggi')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_layer_1_type_c')
                    ->label('Jenis Lapisan Awan 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_layer_1_height_m_hshs')
                    ->label('Tinggi Lapisan Awan 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_layer_1_amt_oktas_ns')
                    ->label('Jumlah Lapisan Awan 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_layer_2_type_c')
                    ->label('Jenis Lapisan Awan 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_layer_2_height_m_hshs')
                    ->label('Tinggi Lapisan Awan 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_layer_2_amt_oktas_ns')
                    ->label('Jumlah Lapisan Awan 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_layer_3_type_c')
                    ->label('Jenis Lapisan Awan 3')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_layer_3_height_m_hshs')
                    ->label('Tinggi Lapisan Awan 3')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_layer_3_amt_oktas_ns')
                    ->label('Jumlah Lapisan Awan 3')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_layer_4_type_c')
                    ->label('Jenis Lapisan Awan 4')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_layer_4_height_m_hshs')
                    ->label('Tinggi Lapisan Awan 4')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_layer_4_amt_oktas_ns')
                    ->label('Jumlah Lapisan Awan 4')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_low_type_1')
                    ->label('Jenis Awan Rendah 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_low_type_2')
                    ->label('Jenis Awan Rendah 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_low_type_3')
                    ->label('Jenis Awan Rendah 3')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_med_type_1')
                    ->label('Jenis Awan Menengah 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_med_type_2')
                    ->label('Jenis Awan Menengah 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_high_type_1')
                    ->label('Jenis Awan Tinggi 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_high_type_2')
                    ->label('Jenis Awan Tinggi 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_low_base_1')
                    ->label('Tinggi Dasar Awan Rendah 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_low_base_2')
                    ->label('Tinggi Dasar Awan Rendah 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_low_base_3')
                    ->label('Tinggi Dasar Awan Rendah 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_med_base_1')
                    ->label('Tinggi Dasar Awan Menengah 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_med_base_2')
                    ->label('Tinggi Dasar Awan Menengah 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_high_base_1')
                    ->label('Tinggi Dasar Awan Tinggi 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_high_base_2')
                    ->label('Tinggi Dasar Awan Tinggi 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('cloud_low_peak_1')
                    ->label('Puncak Awan Rendah 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_low_peak_2')
                    ->label('Puncak Awan Rendah 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_low_cover_1')
                    ->label('Jumlah Awan Rendah 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_low_cover_2')
                    ->label('Jumlah Awan Rendah 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_low_cover_3')
                    ->label('Jumlah Awan Rendah 3')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_med_cover_1')
                    ->label('Jumlah Awan Menengah 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_med_cover_2')
                    ->label('Jumlah Awan Menengah 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_high_cover_1')
                    ->label('Jumlah Awan Tinggi 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_high_cover_2')
                    ->label('Jumlah Awan Tinggi 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_low_dir_1')
                    ->label('Arah Awan Rendah 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_low_dir_2')
                    ->label('Arah Awan Rendah 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_low_dir_3')
                    ->label('Arah Awan Rendah 3')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_med_dir_1')
                    ->label('Arah Awan Menengah 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_med_dir_2')
                    ->label('Arah Awan Menengah 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_high_dir_1')
                    ->label('Arah Awan Tinggi 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_high_dir_2')
                    ->label('Arah Awan Tinggi 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_elevation_angle_ec_1')
                    ->label('Sudut Elevasi Awan 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_elevation_angle_ec_2')
                    ->label('Sudut Elevasi Awan 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_low_dir_true_da_1')
                    ->label('Arah Sebenarnya Awan Rendah 1')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_low_dir_true_da_2')
                    ->label('Arah Sebenarnya Awan Rendah 2')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('cloud_vertical_vis')
                    ->label('Jarak Penglihatan Vertikal')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('evaporation_eq_indicator_ie')
                    ->label('iE')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('evaporation_24hours_mm_eee')
                    ->label('EEE')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('land_cond')
                    ->label('Keadaan Tanah')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('land_note')
                    ->label('Catatan')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('solar_rad_24h_jcm2_f24')
                    ->label('Radiasi Total 24 jam')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),

                Tables\Columns\TextColumn::make('sunshine_h_sss')
                    ->label('SSS')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('observer_id')
                    ->label('Observer')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($state) => SinoptikDataHelper::getObserverNameFromId($state)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Tanggal Update')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_by')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_by')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->columnToggleFormMaxHeight('500px')
            ->columnToggleFormColumns(3)
            ->filters([
                Tables\Filters\Filter::make('Tanggal & Jam')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->displayFormat('d F Y'),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->displayFormat('d F Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['start_date']) && !empty($data['end_date'])) {
                            $start = Carbon::parse($data['start_date'])->startOfDay();
                            $end = Carbon::parse($data['end_date'])->endOfDay();

                            $query->whereBetween('data_timestamp', [$start, $end]);
                        } elseif (!empty($data['start_date'])) {
                            $start = Carbon::parse($data['start_date'])->startOfDay();
                            $query->where('data_timestamp', '>=', $start);
                        } elseif (!empty($data['end_date'])) {
                            $end = Carbon::parse($data['end_date'])->endOfDay();
                            $query->where('data_timestamp', '<=', $end);
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?array {
                        $indicators = [];

                        if (!empty($data['start_date']) && !empty($data['end_date'])) {
                            $indicators[] = 'Tanggal: ' . Carbon::parse($data['start_date'])->format('d F Y') .
                                ' - ' . Carbon::parse($data['end_date'])->format('d F Y');
                        } elseif (!empty($data['start_date'])) {
                            $indicators[] = 'Dari Tanggal: ' . Carbon::parse($data['start_date'])->format('d F Y');
                        } elseif (!empty($data['end_date'])) {
                            $indicators[] = 'Sampai Tanggal: ' . Carbon::parse($data['end_date'])->format('d F Y');
                        }

                        return $indicators ?: null;
                    })

            ])
            ->deferFilters(true)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('sync')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-arrow-path')
                    ->modalHeading('Sinkronisasi Data Sinoptik')
                    ->modalDescription('Sinkronkan ulang dengan data terbaru dari excel?')
                    ->modalSubmitActionLabel('Ya')
                    ->action(function (DataSinoptik $record) {
                        $formattedTimestamp = DateHelper::formatDataTimestamp($record->data_timestamp);
                        FetchSinoptikDataJob::dispatchSync($formattedTimestamp, auth()->id());
                    }),
                Tables\Actions\Action::make('send')
                    ->label('Kirim')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-paper-airplane')
                    ->modalHeading('Kirim Data Sinoptik')
                    ->modalDescription('Bila data sudah pernah terkirim sebelumnya, maka data akan diupdate.')
                    ->modalWidth('xl')
                    ->modalSubmitActionLabel('Kirim')
                    ->visible(fn(DataSinoptik $record) => $record->data_timestamp->gte(now()->subDay()))
                    ->action(function (DataSinoptik $record, BmkgsatuApiService $apiService) {
                        if ($record->data_timestamp->lt(now()->subDay())) {
                            Notification::make()
                                ->title('Hanya bisa mengirim sinoptik 24 jam terakhir')
                                ->warning()
                                ->send();

                            return;
                        }

                        try {
                            // 1. Mutasi data menggunakan helper bawaan
                            $sinoptikDataArray = SinoptikDataHelper::mutateSinoptikDataBeforeSent($record->toArray());

                            // 2. Override bmkgsoft_id dari setting stasiun (mencegah ID default = 1)
                            $stationBmkgsoftId = (int) (setting('bmkgsoft_id') ?? setting('station_bmkgsoft_id') ?? 1);
                            $sinoptikDataArray['bmkgsoft_id'] = $stationBmkgsoftId;

                            // 3. Format timestamp ke format datetime standar MySQL (Y-m-d H:i:s)
                            $sinoptikDataArray['data_timestamp'] = Carbon::parse($record->data_timestamp)
                                ->setTimezone('UTC')
                                ->format('Y-m-d H:i:s');

                            logger()->info(">>> FINALISED PAYLOAD SINOPTIK:", $sinoptikDataArray);

                            // 4. Eksekusi Pengiriman Langsung ke API BMKG Satu
                            $response = $apiService->sendSinoptikData($sinoptikDataArray);

                            logger()->info("Respon API Sinoptik BMKG Satu:", (array) $response);

                            $formattedHour = Carbon::parse($record->data_timestamp)->format('H:i');

                            Notification::make()
                                ->title("Data Sinoptik jam {$formattedHour} UTC Berhasil Terkirim")
                                ->success()
                                ->duration(5000)
                                ->send();

                        } catch (\Throwable $e) {
                            logger()->error("Gagal mengirim Sinoptik via tombol Filament: " . $e->getMessage(), [
                                'exception' => $e
                            ]);

                            Notification::make()
                                ->title("Pengiriman Sinoptik Gagal!")
                                ->body($e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataSinoptiks::route('/'),
            'create' => Pages\CreateDataSinoptik::route('/create'),
            'edit' => Pages\EditDataSinoptik::route('/{record}/edit'),
        ];
    }
}
