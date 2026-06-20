<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Setel Ulang Kata Sandi - Citech 2026</title>
    <style type="text/css">
        @media only screen and (max-width: 600px) {
            .email-body {
                width: 100% !important;
                padding: 12px !important;
            }
            .email-content-cell {
                padding: 16px !important;
            }
            .button {
                width: 100% !important;
                text-align: center !important;
            }
        }
    </style>
</head>
<body style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; color: #334155; margin: 0; padding: 0; width: 100%; -webkit-text-size-adjust: none; -ms-text-size-adjust: none;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f1f5f9; margin: 0; padding: 40px 0; width: 100%;">
        <tr>
            <td align="center">
                <table class="email-body" width="570" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #ffffff; border-radius: 24px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04); overflow: hidden; width: 570px;">
                    <!-- Header Banner -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #1e4d8c 100%); padding: 36px 40px; text-align: center;">
                            <h2 style="color: #ffffff; font-size: 26px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Carnival Technology</h2>
                            <p style="color: #eab308; font-size: 14px; font-weight: 700; margin: 6px 0 0 0; text-transform: uppercase; letter-spacing: 1px;">Wujudkan Ide, Ciptakan Inovasi</p>
                        </td>
                    </tr>
                    
                    <!-- Content Body -->
                    <tr>
                        <td class="email-content-cell" style="padding: 40px;">
                            <h1 style="color: #0f172a; font-size: 20px; font-weight: 700; margin-top: 0; margin-bottom: 16px; letter-spacing: -0.3px;">Halo, {{ $name }}!</h1>
                            <p style="font-size: 14px; line-height: 1.6; margin-top: 0; margin-bottom: 24px;">Kami menerima permintaan untuk menyetel ulang kata sandi akun Anda di **Carnival Technology UNEJ 2026**. Silakan klik tombol di bawah ini untuk melanjutkan proses penyetelan ulang kata sandi:</p>
                            
                            <!-- Action Button Container -->
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $resetUrl }}" class="button" style="background-color: #1e4d8c; border: 12px solid #1e4d8c; border-left: 28px solid #1e4d8c; border-right: 28px solid #1e4d8c; border-radius: 12px; color: #ffffff; display: inline-block; font-size: 14px; font-weight: 700; text-decoration: none; transition: background-color 0.3s; box-shadow: 0 4px 12px rgba(30, 77, 140, 0.25);">Setel Ulang Password</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 14px; line-height: 1.6; margin-top: 0; margin-bottom: 12px;">Tautan reset kata sandi ini akan kedaluwarsa dalam waktu **60 menit**.</p>
                            <p style="font-size: 14px; line-height: 1.6; margin-top: 0; margin-bottom: 24px; color: #64748b;">Jika Anda tidak merasa mengajukan permintaan ini, tidak ada tindakan lebih lanjut yang diperlukan dan kata sandi Anda akan tetap aman.</p>
                            
                            <!-- Fallback Link Section -->
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-top: 1px solid #e2e8f0; margin-top: 32px; padding-top: 20px;">
                                <tr>
                                    <td>
                                        <p style="font-size: 12px; line-height: 1.5; color: #64748b; margin: 0;">Jika Anda mengalami kendala mengeklik tombol di atas, salin dan tempel URL berikut ke browser Anda:</p>
                                        <p style="font-size: 12px; line-height: 1.5; margin: 8px 0 0 0; word-break: break-all;">
                                            <a href="{{ $resetUrl }}" style="color: #1e4d8c; text-decoration: underline; font-weight: 600;">{{ $resetUrl }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer Section -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #f1f5f9; padding: 24px 40px; text-align: center;">
                            <p style="font-size: 12px; line-height: 1.5; color: #94a3b8; margin: 0;">Salam hangat,</p>
                            <p style="font-size: 12px; line-height: 1.5; color: #475569; font-weight: 700; margin: 4px 0 0 0;">Panitia Carnival Technology UNEJ 2026</p>
                            <p style="font-size: 11px; color: #94a3b8; margin-top: 16px; margin-bottom: 0;">&copy; 2026 Carnival Technology. Hak Cipta Dilindungi.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
