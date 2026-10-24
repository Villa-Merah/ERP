<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Izinkan akses aplikasi</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f3f6f0;font-family:system-ui;color:#203329}main{max-width:440px;margin:24px;padding:32px;background:white;border:1px solid #e0e7df;border-radius:20px}p{line-height:1.7;color:#637267}button{padding:12px 20px;border-radius:8px;border:0;cursor:pointer;font:inherit}.actions{display:flex;gap:12px;margin-top:24px}.approve{background:#263e31;color:white}</style></head>
<body><main><small>{{ config('app.name') }}</small><h1>Hubungkan {{ $client->name }}?</h1><p>Anda masuk sebagai <strong>{{ $user->email }}</strong>. Aplikasi akan menerima identitas, nama, dan email untuk proses login.</p>
<div class="actions">
<form method="POST" action="{{ route('passport.authorizations.approve') }}">@csrf<input type="hidden" name="client_id" value="{{ $client->getKey() }}"><input type="hidden" name="state" value="{{ $request->state }}"><input type="hidden" name="auth_token" value="{{ $authToken }}"><button class="approve">Izinkan akses</button></form>
<form method="POST" action="{{ route('passport.authorizations.deny') }}">@csrf @method('DELETE')<input type="hidden" name="client_id" value="{{ $client->getKey() }}"><input type="hidden" name="state" value="{{ $request->state }}"><input type="hidden" name="auth_token" value="{{ $authToken }}"><button>Batal</button></form>
</div></main></body></html>
