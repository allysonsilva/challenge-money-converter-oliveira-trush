[program:laravel-octane]
process_name=%(program_name)s_%(process_num)02d
command=php -d variables_order=EGPCS artisan octane:start --server=swoole --max-requests=100000 --host=0.0.0.0 --port=8000
directory=%(ENV_REMOTE_SRC)s
user=%(ENV_USER_NAME)s
environment=LARAVEL_OCTANE="1"
autostart=true
autorestart=true

stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

; (300 seconds) timeout to give workers time to finish
; Graceful shutdown
stopwaitsecs=300
