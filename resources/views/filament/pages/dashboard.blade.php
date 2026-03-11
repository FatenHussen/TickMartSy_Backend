@extends('filament::layouts.app')

@section('content')
    <div>
        @livewire('filament.pages.dashboard')
        @livewire('save-fcm-token')
    </div>

    @include('filament.components.fcm-script')
@endsection
