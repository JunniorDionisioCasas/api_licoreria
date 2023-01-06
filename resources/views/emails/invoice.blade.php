@component('mail::message')
# Gracias por tu compra

Te enviamos tu recibo de compra.
@component('mail::panel')
Se adjunta el comprobante de su compra realizada.
@endcomponent

@component('mail::button', ['url' => 'https://licoreriasansebastian.com/historial_compras'])
Ver mis compras
@endcomponent

Saludos cordiales,<br>
{{ config('app.name') }}
@endcomponent
