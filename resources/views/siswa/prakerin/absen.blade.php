<x-simans-layout title="{{ $type === 'check_in' ? 'Absen Masuk' : 'Absen Pulang' }} Prakerin">
    <div class="mb-5">
        <a href="{{ route('siswa.prakerin.index') }}" class="text-gray-400 text-sm hover:text-gray-900 flex items-center gap-1 mb-3 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h1 class="text-xl font-bold text-gray-900">{{ $type === 'check_in' ? 'Absen Masuk' : 'Absen Pulang' }}</h1>
        <p class="text-gray-500 text-sm mt-0.5">{{ $placement->location->name }}</p>
        @if ($type === 'check_in' && $placement->location->checkin_time)
            <p class="text-gray-400 text-xs mt-0.5">
                Jam masuk: {{ $placement->location->checkin_time }}
                @if ($placement->location->checkin_late_after)
                    · Toleransi: {{ $placement->location->checkin_late_after }}
                @endif
            </p>
        @endif
    </div>

    <div id="gps-status" class="mb-4 p-3 rounded-xl bg-white border border-gray-200 flex items-center gap-3">
        <div id="gps-icon" class="w-8 h-8 rounded-lg bg-white flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-gray-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            </svg>
        </div>
        <div>
            <p id="gps-text" class="text-gray-500 text-sm">Mengambil lokasi GPS...</p>
            <p id="gps-detail" class="text-gray-400 text-xs mt-0.5"></p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-4">
        <div class="relative">
            <video id="video" autoplay playsinline class="w-full aspect-[3/4] object-cover bg-black"></video>
            <canvas id="canvas" class="hidden"></canvas>
            <img id="preview" class="hidden w-full aspect-[3/4] object-cover" alt="Selfie preview"/>
            <div id="guide-overlay" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="w-48 h-48 rounded-full border-2 border-white/30 border-dashed"></div>
            </div>
            <button id="btn-retake" onclick="retakePhoto()"
                    class="hidden absolute top-3 right-3 px-3 py-1.5 bg-white/80 text-gray-900 text-xs rounded-lg border border-gray-200 backdrop-blur-sm">
                Ulang Foto
            </button>
        </div>
        <div class="p-4">
            <button id="btn-capture" onclick="capturePhoto()"
                    class="w-full py-3 bg-blue-700 hover:bg-blue-600 text-gray-900 font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Ambil Foto Selfie
            </button>
            <button id="btn-submit" onclick="submitAbsen()" disabled
                    class="hidden w-full py-3 bg-blue-700 hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-gray-900 font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span id="btn-submit-text">Kirim Absensi</span>
            </button>
        </div>
    </div>

    @if ($placement->location->latitude && $placement->location->longitude)
        <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
            <p class="text-gray-400 text-xs">Radius check-in: <span class="text-gray-600">{{ $placement->location->radius_meters ?? 300 }}m</span> dari {{ $placement->location->name }}</p>
        </div>
    @endif

    <div id="alert" class="hidden mt-4 p-3 rounded-xl text-sm border"></div>

    <script>
        const TYPE='{{ $type }}', CSRF='{{ csrf_token() }}';
        const STORE_URL='{{ route("siswa.prakerin.absen.store") }}';
        const BACK_URL='{{ route("siswa.prakerin.index") }}';
        let photoData=null, gpsLat=null, gpsLng=null, gpsAccuracy=null, stream=null;

        function initGps() {
            if (!navigator.geolocation) { setGpsStatus('error','GPS tidak didukung',''); return; }
            navigator.geolocation.getCurrentPosition(
                pos => { gpsLat=pos.coords.latitude; gpsLng=pos.coords.longitude; gpsAccuracy=pos.coords.accuracy;
                    setGpsStatus('ok','Lokasi ditemukan','Akurasi ±'+Math.round(gpsAccuracy)+'m'); checkCanSubmit(); },
                err => setGpsStatus('error','Gagal mengambil lokasi GPS','Pastikan izin lokasi sudah diberikan'),
                { enableHighAccuracy:true, timeout:15000 }
            );
        }
        function setGpsStatus(s,t,d) {
            const icon=document.getElementById('gps-icon'), txt=document.getElementById('gps-text'), det=document.getElementById('gps-detail');
            txt.textContent=t; det.textContent=d;
            if(s==='ok') { icon.className='w-8 h-8 rounded-lg bg-blue-600/10 flex items-center justify-center flex-shrink-0';
                icon.innerHTML='<svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                txt.className='text-blue-600 text-sm'; }
            else { icon.className='w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center flex-shrink-0';
                icon.innerHTML='<svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                txt.className='text-red-400 text-sm'; }
        }
        async function initCamera() {
            try { stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:'user'},audio:false});
                document.getElementById('video').srcObject=stream; }
            catch(e) { showAlert('error','Gagal akses kamera: '+e.message); }
        }
        function capturePhoto() {
            const v=document.getElementById('video'), c=document.getElementById('canvas');
            c.width=v.videoWidth; c.height=v.videoHeight; c.getContext('2d').drawImage(v,0,0);
            photoData=c.toDataURL('image/jpeg',0.85);
            document.getElementById('preview').src=photoData;
            document.getElementById('preview').classList.remove('hidden');
            v.classList.add('hidden'); document.getElementById('guide-overlay').classList.add('hidden');
            document.getElementById('btn-capture').classList.add('hidden');
            document.getElementById('btn-submit').classList.remove('hidden');
            document.getElementById('btn-retake').classList.remove('hidden');
            if(stream) stream.getTracks().forEach(t=>t.stop()); checkCanSubmit();
        }
        function retakePhoto() {
            photoData=null;
            document.getElementById('preview').classList.add('hidden');
            document.getElementById('video').classList.remove('hidden');
            document.getElementById('guide-overlay').classList.remove('hidden');
            document.getElementById('btn-capture').classList.remove('hidden');
            document.getElementById('btn-submit').classList.add('hidden');
            document.getElementById('btn-retake').classList.add('hidden');
            document.getElementById('btn-submit').disabled=true;
            initCamera();
        }
        function checkCanSubmit() {
            if(photoData && gpsLat!==null) document.getElementById('btn-submit').disabled=false;
        }
        async function submitAbsen() {
            if(!photoData||gpsLat===null) { showAlert('error','Foto dan GPS diperlukan.'); return; }
            const btn=document.getElementById('btn-submit');
            btn.disabled=true; document.getElementById('btn-submit-text').textContent='Mengirim...';
            try {
                const res=await fetch(STORE_URL,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                    body:JSON.stringify({type:TYPE,selfie:photoData,latitude:gpsLat,longitude:gpsLng,accuracy:gpsAccuracy})});
                const data=await res.json();
                if(data.success) { showAlert(data.warning?'warning':'success',data.message); setTimeout(()=>{window.location.href=BACK_URL;},2000); }
                else { showAlert('error',data.message||'Gagal.'); btn.disabled=false; document.getElementById('btn-submit-text').textContent='Kirim Absensi'; }
            } catch(e) { showAlert('error','Error: '+e.message); btn.disabled=false; document.getElementById('btn-submit-text').textContent='Kirim Absensi'; }
        }
        function showAlert(type,msg) {
            const el=document.getElementById('alert');
            el.className='mt-4 p-3 rounded-xl text-sm border';
            if(type==='success') el.classList.add('bg-blue-600/10','text-blue-600','border-blue-200');
            else if(type==='warning') el.classList.add('bg-amber-500/10','text-amber-400','border-amber-500/20');
            else el.classList.add('bg-red-500/10','text-red-400','border-red-500/20');
            el.textContent=msg; el.classList.remove('hidden'); el.scrollIntoView({behavior:'smooth'});
        }
        document.addEventListener('DOMContentLoaded',()=>{ initGps(); initCamera(); });
    </script>
</x-simans-layout>
