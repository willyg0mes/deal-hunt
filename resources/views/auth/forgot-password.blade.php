@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-gray-900 via-red-900 to-red-800">
    <div class="w-full max-w-md bg-gray-900 border border-red-700 rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-red-400 mb-6 text-center">Recuperar senha</h2>
        @if (session('status'))
            <div class="mb-4 text-green-400 font-bold text-center">
                {{ session('status') }}
            </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf
            <div>
                <x-input-label for="email" :value="__('Email')" class="text-gray-200" />
                <x-text-input id="email" class="block mt-1 w-full bg-gray-800 border border-red-700 text-gray-200" type="email" name="email" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
            </div>
            <x-primary-button class="w-full bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-4 rounded transition duration-200">
                Enviar link de recuperação
            </x-primary-button>
        </form>
    </div>
</div>
@endsection
