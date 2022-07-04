<h3>Hi {{ $trainer->first_name.' '.$trainer->last_name }},</h3>

<p>We welcome you on JOGO as a trainer.</p>

<p>Please setup a password for your account by clicking to this link <a href="{{ ((env('APP_ENV') == 'development') ? 'https://www.dev.trainer.jogo.ai/setup-password' : ((env('APP_ENV') == 'staging') ? 'https://www.test.trainer.jogo.ai/setup-password' : 'https://trainer.jogo.ai/setup-password')).'?token='.$trainer->remember_token.'&otp='.$trainer->verification_code }}">{{ ((env('APP_ENV') == 'development') ? 'https://www.dev.trainer.jogo.ai/setup-password' : ((env('APP_ENV') == 'staging') ? 'https://www.test.trainer.jogo.ai/setup-password' : 'https://trainer.jogo.ai/setup-password')).'?token='.$trainer->remember_token.'&otp='.$trainer->verification_code }}</a></p>

<p>Link will expire in 15 minutes.</p>