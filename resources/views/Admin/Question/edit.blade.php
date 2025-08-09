@extends('layout.layout')
@extends('layout.navbar')

@section('header')
@section('sidenav')
@endsection

<main class="flex-1 p-6 min-h-screen bg-white rounded-l-3xl shadow-inner flex flex-col">

    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6 border-b pb-3">
        <h2 class="text-2xl font-bold text-green-800 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-list"></i>
            Detail Inspeksi: {{ $inspectionType->name }}
        </h2>
        <a href="{{ route('inspection-types.index') }}"
            class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded border border-gray-300 transition">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('inspection-types.submit_detail') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="inspection_type_id" value="{{ $inspectionType->id }}">

        <!-- HEADER CONTAINER -->
        <div id="headersContainer" class="space-y-6">
            @foreach ($inspectionType->headers as $hIndex => $header)
                <div class="bg-gray-50 p-5 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fa-solid fa-folder"></i>
                            Header {{ $hIndex + 1 }}
                        </h4>
                        <button type="button" onclick="removeHeader(this)"
                            class="flex items-center gap-1 text-red-600 hover:text-red-700 text-sm font-medium">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </button>
                    </div>

                    <label class="block text-sm font-medium text-gray-600 mb-2">Nama Header</label>
                    <input type="text" name="headers[{{ $hIndex }}][title]" value="{{ $header->title }}"
                        class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-green-300 outline-none"
                        placeholder="Contoh: Kondisi Fisik">

                    <div class="mt-4 space-y-3" id="questions-{{ $hIndex }}">
                        @foreach ($header->questions as $qIndex => $question)
                            <div class="flex items-center gap-2">
                                <input type="text" name="headers[{{ $hIndex }}][questions][{{ $qIndex }}][text]"
                                    value="{{ $question->question_text }}"
                                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-green-300 outline-none"
                                    placeholder="Masukkan pertanyaan di sini...">
                                <button type="button" onclick="removeQuestion(this)"
                                    class="text-red-500 hover:text-red-600">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" onclick="addQuestion({{ $hIndex }})"
                        class="mt-4 flex items-center gap-2 text-sm text-blue-600 hover:underline">
                        <i class="fa-solid fa-plus"></i> Tambah Pertanyaan
                    </button>
                </div>
            @endforeach
        </div>

        <!-- FOOTER BUTTONS -->
        <div class="mt-6 flex justify-between items-center">
            <button type="button" onclick="addHeader()"
                class="flex items-center gap-2 text-sm text-blue-600 hover:underline">
                <i class="fa-solid fa-folder-plus"></i> Tambah Header Baru
            </button>

            <button type="submit"
                class="flex items-center gap-2 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fa-solid fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</main>

<script>
    let headerIndex = {{ count($inspectionType->headers) }};

    function addHeader() {
        const container = document.getElementById('headersContainer');
        const headerHTML = `
            <div class="bg-gray-50 p-5 rounded-lg shadow-sm border border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-lg font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fa-solid fa-folder"></i> Header Baru
                    </h4>
                    <button type="button" onclick="removeHeader(this)"
                        class="flex items-center gap-1 text-red-600 hover:text-red-700 text-sm font-medium">
                        <i class="fa-solid fa-trash"></i> Hapus
                    </button>
                </div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Nama Header</label>
                <input type="text" name="headers[${headerIndex}][title]"
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-green-300 outline-none"
                    placeholder="Contoh: Kondisi Fisik">

                <div class="mt-4 space-y-3" id="questions-${headerIndex}">
                    <div class="flex items-center gap-2">
                        <input type="text" name="headers[${headerIndex}][questions][0][text]"
                            class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-green-300 outline-none"
                            placeholder="Masukkan pertanyaan di sini...">
                        <button type="button" onclick="removeQuestion(this)"
                            class="text-red-500 hover:text-red-600">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>

                <button type="button" onclick="addQuestion(${headerIndex})"
                    class="mt-4 flex items-center gap-2 text-sm text-blue-600 hover:underline">
                    <i class="fa-solid fa-plus"></i> Tambah Pertanyaan
                </button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', headerHTML);
        headerIndex++;
    }

    function addQuestion(hIndex) {
        const container = document.getElementById(`questions-${hIndex}`);
        const qIndex = container.children.length;
        const questionHTML = `
            <div class="flex items-center gap-2">
                <input type="text" name="headers[${hIndex}][questions][${qIndex}][text]"
                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-green-300 outline-none"
                    placeholder="Masukkan pertanyaan di sini...">
                <button type="button" onclick="removeQuestion(this)"
                    class="text-red-500 hover:text-red-600">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', questionHTML);
    }

    function removeHeader(button) {
        button.closest('.bg-gray-50').remove();
    }

    function removeQuestion(button) {
        button.closest('.flex').remove();
    }
</script>

@endsection
