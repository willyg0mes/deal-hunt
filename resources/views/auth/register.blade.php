{{-- Mantém a rota /register; reutiliza a mesma UI de auth --}}
@include('auth.login', ['showRegister' => true])
