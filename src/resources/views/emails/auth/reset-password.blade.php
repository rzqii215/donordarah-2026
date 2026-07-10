@component('mail::message')
# Reset Password Akun Donor Darah

Halo {{ $user->name ?? 'Pengguna' }},

Kami menerima permintaan untuk mereset password akun Anda pada sistem **Donor Darah**.

Silakan klik tombol di bawah ini untuk membuat password baru.

@component('mail::button', ['url' => $resetUrl, 'color' => 'error'])
Reset Password
@endcomponent

Link reset password ini berlaku selama **{{ $expireMinutes }} menit**.

Jika Anda tidak merasa meminta reset password, abaikan email ini. Password akun Anda tidak akan berubah.

Terima kasih,  
**Donor Darah**

@slot('subcopy')
Jika tombol tidak bisa diklik, salin dan buka link berikut di browser:

{{ $resetUrl }}
@endslot
@endcomponent
