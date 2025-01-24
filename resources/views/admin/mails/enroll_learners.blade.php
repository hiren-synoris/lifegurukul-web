
@extends('admin.mails.mail-layout')
@section('title', 'Learner Enrolled Successfully')
@section('mail-body')

<!-- START BOX SHOWCASE -->
        <tr>
            <td style="height:20px"></td>
        </tr>
        <tr>
            <td align="left"
                style="text-align: center; font-size:32px; font-weight:bold; color:#58a851;">
                <a href="{{ url('backoffice/login') }}" class="h1">{{ config('app.name') }}</a></td>
        </tr>
        <tr>
            <td style="height:20px"></td>
        </tr>
        <tr>
            <td align="left"
                style="font-size:20px; text-align: center; font-weight: 400; line-height:20px;">
                <p>{{ $content }}</p>
            </td>
        </tr>

<!-- END 3 BOX SHOWCASE -->

@endsection
