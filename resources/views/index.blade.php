<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Product Quality Inspection</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-green-700 min-h-screen flex items-center justify-center px-4">

  <div class="bg-white max-w-md w-full rounded-xl shadow-lg p-8 text-center space-y-6">
    
    <!-- Logo -->
    <div class="w-20 h-20 mx-auto rounded-xl bg-green-200 p-3 flex items-center justify-center">
      <img src="/images/kmi.png" alt="Logo KMI" class="object-contain h-full">
    </div>

    <!-- Heading -->
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Product Quality Inspection</h1>
    <p class="text-sm text-green-700 font-semibold leading-tight">
      Selamat Datang di Sistem Product Quality Inspection<br>PT. Kayu Mabel Indonesia
    </p>
    <p class="text-sm text-gray-600">Silahkan masuk menggunakan akun SAP anda</p>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <strong>Terjadi Kesalahan:</strong>
            <ul class="mt-1 list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <!-- Login Form -->
    <form action="{{ route('submit-login') }}" method="POST" class="space-y-4 text-left">
      @csrf

      <!-- Username -->
      <div>
        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
        <input type="text" name="username" id="username" placeholder="Masukkan Username Anda" required
          class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm focus:ring-green-500 focus:border-green-500 transition" />
        @error('username')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <div class="relative">
          <input type="password" name="password" id="password" placeholder="Masukkan Password Anda" required
            class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 pr-10 shadow-sm focus:ring-green-500 focus:border-green-500 transition" />
          <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-green-600">
            <i data-lucide="eye" id="eye-icon" class="w-5 h-5"></i>
          </button>
          @error('password')
              <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <!-- Role -->
      <div>
        <label for="role" class="block text-sm font-medium text-gray-700">Masuk Sebagai :</label>
        <div class="relative">
          <select name="role" id="role" class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 pr-10 shadow-sm focus:ring-green-500 focus:border-green-500 transition">
            <option value="admin">Super Admin</option>
            <option value="inspector">Inspector</option>
            <option value="buyer">Super Admin</option>
          </select>
        </div>
      </div>

      <!-- Submit Button -->
        <div>
            <button type="submit"
                class="w-full bg-green-300 hover:bg-green-700 text-black font-bold py-2 rounded-full transition-all duration-200 ease-in-out active:scale-95 shadow-md">
                Masuk
            </button>
        </div>
    </form>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      lucide.createIcons();
    });

    function togglePassword() {
      const input = document.getElementById("password");
      const icon = document.getElementById("eye-icon");
      if (input.type === "password") {
        input.type = "text";
        icon.setAttribute("data-lucide", "eye");
      } else {
        input.type = "password";
        icon.setAttribute("data-lucide", "eye-off");
      }
      lucide.createIcons();
    }
  </script>
</body>
</html>
