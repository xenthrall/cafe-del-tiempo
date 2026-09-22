@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', 'Zona solo para baristas')
@section('hint', $exception->getMessage() ?: 'No tienes acceso a esta parte de la cafetería.')
