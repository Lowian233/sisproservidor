<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #333; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #667eea; padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">Nueva Calificación de Servicio</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin: 0 0 20px 0;">Se ha recibido una nueva calificación de servicio:</p>
                            
                            <h2 style="margin: 30px 0 20px 0; font-size: 18px; font-weight: 600;">Calificación:</h2>
                            
                            @if($calificacion->score)
                                @if($calificacion->score == 1)
                                    <div style="text-align: center; margin: 30px 0;">
                                        <div style="font-size: 50px; background-color: #dc3545; border-radius: 50%; padding: 10px 15px; display: inline-block; width: 70px; height: 70px; line-height: 50px;">😞</div>
                                        <p style="color: #dc3545; font-weight: bold; margin-top: 10px; font-size: 16px;"><strong>Malo</strong></p>
                                    </div>
                                @elseif($calificacion->score == 2)
                                    <div style="text-align: center; margin: 30px 0;">
                                        <div style="font-size: 50px; background-color: #ffc107; border-radius: 50%; padding: 10px 15px; display: inline-block; width: 70px; height: 70px; line-height: 50px;">😐</div>
                                        <p style="color: #ffc107; font-weight: bold; margin-top: 10px; font-size: 16px;"><strong>Regular</strong></p>
                                    </div>
                                @elseif($calificacion->score == 3)
                                    <div style="text-align: center; margin: 30px 0;">
                                        <div style="font-size: 50px; background-color: #28a745; border-radius: 50%; padding: 10px 15px; display: inline-block; width: 70px; height: 70px; line-height: 50px;">😊</div>
                                        <p style="color: #28a745; font-weight: bold; margin-top: 10px; font-size: 16px;"><strong>Bueno</strong></p>
                                    </div>
                                @endif
                            @endif

                            @if($calificacion->comment)
                                <div style="background-color: #f0f3f8; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0;">
                                    <strong>Comentario del Cliente:</strong><br>
                                    "{{ $calificacion->comment }}"
                                </div>
                            @endif

                            @if($calificacion->not_received)
                                <div style="background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0;">
                                    <strong>⚠️ Importante:</strong> El cliente reportó que el Recibo de Material (RM) no llegó.
                                </div>
                            @endif

                            <p style="margin: 30px 0 20px 0;">Puedes ver más detalles en el sistema de gestión.</p>

                            <p style="margin: 30px 0 10px 0;">Saludos cordiales,<br>
                            <strong>Area De Sistemas y Desarrollo de Prosarc S.A. ESP</strong></p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px 0; font-size: 12px; color: #6c757d; line-height: 1.5;">
                                La información de este mensaje es privilegiada y confidencial.
                            </p>
                            <p style="margin: 0 0 10px 0; font-size: 12px; color: #6c757d; line-height: 1.5;">
                                Este correo electrónico se envió desde una dirección que no acepta correos electrónicos entrantes. Por favor, no responda a este mensaje.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #6c757d; line-height: 1.5;">
                                Si tiene alguna pregunta, inquietud o si recibió esta notificación por error comuníquese con: coordinadorse@prosarc.com.co
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>