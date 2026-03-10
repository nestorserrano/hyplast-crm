<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $factura->FACTURA }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            padding: 10mm;
        }

        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 10px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header-left {
            display: table-cell;
            width: 45%;
            vertical-align: top;
        }

        .header-left img {
            max-width: 120px;
            height: auto;
            margin-bottom: 5px;
        }

        .header-left .company-info {
            font-size: 8px;
            line-height: 1.4;
        }

        .header-left .company-info strong {
            font-size: 9px;
        }

        .header-center {
            display: table-cell;
            width: 25%;
            vertical-align: middle;
            text-align: center;
            padding: 0 10px;
        }

        .header-center .rnc {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .header-center .invoice-title {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }

        .header-center .page-info {
            font-size: 8px;
            margin-top: 3px;
        }

        .header-right {
            display: table-cell;
            width: 30%;
            vertical-align: top;
        }

        .header-right table {
            width: 100%;
            border: 1px solid #000;
            font-size: 9px;
        }

        .header-right table td {
            padding: 3px 5px;
            border-bottom: 1px solid #ccc;
        }

        .header-right table td:first-child {
            font-weight: bold;
            background-color: #f0f0f0;
            width: 50%;
        }

        .header-right table tr:last-child td {
            border-bottom: none;
        }

        .customer-section {
            margin: 10px 0;
            border: 1px solid #000;
            padding: 8px;
        }

        .customer-section table {
            width: 100%;
            font-size: 9px;
        }

        .customer-section table td {
            padding: 2px 5px;
        }

        .customer-section table td:nth-child(odd) {
            font-weight: bold;
            width: 18%;
        }

        .seller-section {
            margin: 10px 0;
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 9px;
        }

        .seller-section table {
            width: 100%;
        }

        .seller-section td {
            padding: 2px 5px;
        }

        .seller-section td:nth-child(odd) {
            font-weight: bold;
            width: 20%;
        }

        .items-section {
            margin: 10px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .items-table thead {
            background-color: #f0f0f0;
        }

        .items-table th {
            border: 1px solid #000;
            padding: 5px 3px;
            font-weight: bold;
            text-align: center;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 4px 3px;
        }

        .items-table td.text-left {
            text-align: left;
        }

        .items-table td.text-center {
            text-align: center;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .footer-section {
            margin-top: 10px;
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            width: 55%;
            vertical-align: top;
        }

        .footer-boxes {
            border: 1px solid #000;
            padding: 5px;
            margin-bottom: 10px;
        }

        .footer-boxes table {
            width: 100%;
            font-size: 8px;
        }

        .footer-boxes td {
            padding: 2px 5px;
        }

        .footer-boxes td:first-child {
            font-weight: bold;
            width: 30%;
        }

        .footer-notes {
            font-size: 7px;
            line-height: 1.3;
            margin-top: 5px;
        }

        .footer-right {
            display: table-cell;
            width: 45%;
            vertical-align: top;
            padding-left: 10px;
        }

        .totals-table {
            width: 100%;
            border: 1px solid #000;
            font-size: 10px;
        }

        .totals-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #ccc;
        }

        .totals-table td:first-child {
            font-weight: bold;
            text-align: right;
            width: 60%;
        }

        .totals-table td:last-child {
            text-align: right;
        }

        .totals-table tr:last-child {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 11px;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .signatures {
            margin-top: 20px;
            display: table;
            width: 100%;
            font-size: 9px;
        }

        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 0 10px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }

        /* Botón de impresión flotante */
        .print-button-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .print-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .print-button:hover {
            background: #0056b3;
            box-shadow: 0 6px 8px rgba(0,0,0,0.3);
        }

        .print-button:active {
            transform: scale(0.98);
        }

        .close-button {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            margin-left: 10px;
        }

        .close-button:hover {
            background: #5a6268;
        }

        @media print {
            body {
                padding: 0;
            }
            .invoice-container {
                border: none;
            }
            .print-button-container {
                display: none !important;
            }
            @page {
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <!-- Botones flotantes de acción -->
    <div class="print-button-container">
        <button class="print-button" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Imprimir
        </button>
        <button class="close-button" onclick="window.close()">
            Cerrar
        </button>
    </div>

    <div class="invoice-container">
        <!-- HEADER -->
        <div class="header">
            <!-- Logo y datos de la empresa -->
            <div class="header-left">
                @php
                    $logoPath = null;
                    if ($conjunto && $conjunto->LOGO) {
                        // Intentar encontrar el logo
                        $possibleLogos = [
                            $conjunto->LOGO, // Logo_C01.JPG
                            'logo' . strtolower($conjunto->CONJUNTO) . '.jpg', // logoc01.jpg
                            'logo' . $conjunto->CONJUNTO . '.jpg', // logoC01.jpg
                            strtolower($conjunto->LOGO), // logo_c01.jpg
                        ];

                        foreach ($possibleLogos as $logoFile) {
                            $fullPath = public_path('imagen/conjunto/' . $logoFile);
                            if (file_exists($fullPath)) {
                                $logoPath = asset('imagen/conjunto/' . $logoFile);
                                break;
                            }
                        }
                    }

                    // Fallback al logo default
                    if (!$logoPath) {
                        $logoPath = asset('images/logo-transparenterec.png');
                    }
                @endphp
                <img src="{{ $logoPath }}" alt="{{ $conjunto->NOMBRE ?? 'Logo' }}">
                <div class="company-info">
                    <strong>{{ $conjunto->NOMBRE ?? 'HYPLAST S.R.L.' }}</strong><br>
                    {{ $conjunto->DIREC1 ?? 'AVENIDA FRANCIA INDUSTRIAL, CALLE LATERAL DERECHA' }}<br>
                    {{ $conjunto->DIREC2 ?? 'S/P, DISTRITO MUNICIPAL, ORIB, REPÚBLICA DOMINICANA' }}<br>
                    Teléfono: {{ $conjunto->TELEFONO ?? '809 246 0850 / 809 339 0168' }}
                </div>
            </div>

            <!-- Centro: RNC y título -->
            <div class="header-center">
                <div class="rnc">RNC: {{ $conjunto->NIT ?? '132008495' }}</div>
                <div class="invoice-title">INVOICE/FACTURA</div>
                <div class="page-info">Page/Pag# 1 of 1</div>
            </div>

            <!-- Derecha: Info de factura -->
            <div class="header-right">
                <table>
                    <tr>
                        <td>INVOICE/FACTURA#:</td>
                        <td>{{ $factura->FACTURA }}</td>
                    </tr>
                    <tr>
                        <td>Trxs/Ref/Pedido#:</td>
                        <td>{{ $factura->PEDIDO ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Purchase Order/
                            <br>Date de Compra/
                            <br>Fecha De Pedido</td>
                        <td>{{ isset($factura->FECHA_PEDIDO) && $factura->FECHA_PEDIDO ? \Carbon\Carbon::parse($factura->FECHA_PEDIDO)->format('d/m/Y') : '' }}</td>
                    </tr>
                    <tr>
                        <td>Terms/Condiciones
                            <br>de Pago:</td>
                        <td>{{ $factura->CONDICION_PAGO ?? 'CREDITO/CREDITO' }}</td>
                    </tr>
                    <tr>
                        <td>Due date/Fecha De
                            <br>Vencimiento:</td>
                        <td>{{ isset($factura->FECHA_VENCE) && $factura->FECHA_VENCE ? \Carbon\Carbon::parse($factura->FECHA_VENCE)->format('d/m/Y') : '' }}</td>
                    </tr>
                    <tr>
                        <td>INCOTERM/TERMINOS:</td>
                        <td>FOB</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- CUSTOMER INFO -->
        <div class="customer-section">
            <table>
                <tr>
                    <td>Customer/Cliente:</td>
                    <td colspan="3"><strong>{{ $customer->NOMBRE }}</strong></td>
                    <td>INVOICE/FACTURA#:</td>
                    <td>{{ $factura->FACTURA }}</td>
                </tr>
                <tr>
                    <td>Tax ID/Ident. Fiscal:</td>
                    <td>{{ $customer->CONTRIBUYENTE ?? $customer->CLIENTE }}</td>
                    <td>RNC/Ced:</td>
                    <td>{{ $customer->CEDULA ?? $customer->CONTRIBUYENTE }}</td>
                    <td>Trxs/Ref/Pedido#:</td>
                    <td>{{ $factura->PEDIDO ?? '' }}</td>
                </tr>
                <tr>
                    <td>Address/Dirección:</td>
                    <td colspan="3">{{ $customer->DIRECCION }}</td>
                    <td>Purchase Date/
                        <br>Condiciones de
                        <br>Pago:</td>
                    <td>{{ $factura->CONDICION_PAGO ?? '' }}</td>
                </tr>
            </table>
        </div>

        <!-- SELLER & DELIVERY INFO -->
        <div class="seller-section">
            <table>
                <tr>
                    <td>Customer Country/País del Cliente:</td>
                    <td>{{ $customer->PAIS ?? 'República Dominicana' }}</td>
                    <td>INCOTERM/TERMINOS:</td>
                    <td>FOB</td>
                </tr>
                <tr>
                    <td>Seller/Vendedor:</td>
                    <td>{{ $factura->VENDEDOR ?? 'ANDREA' }}</td>
                    <td>Port of Discharge/Puerto de
                        <br>Destino:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Origin Country/País Origen:</td>
                    <td>REPUBLICA DOMINICANA</td>
                    <td>Delivery Country/País Destino:</td>
                    <td>{{ $customer->PAIS ?? 'REPUBLICA DOMINICANA' }}</td>
                </tr>
                <tr>
                    <td>Port of Loading/Puerto de Origen:</td>
                    <td>RD - MIAMI</td>
                    <td>Bill of Lading/
                        <br>Conocimiento de
                        <br>Embarque BL:</td>
                    <td></td>
                </tr>
            </table>
        </div>

        <!-- ITEMS TABLE -->
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Qty/Cant</th>
                        <th>Order/Codigo</th>
                        <th>Description / Descripción</th>
                        <th>Packing/Embalaje</th>
                        <th>Unit Price/<br>Precio</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $subtotal = 0;
                    @endphp
                    @foreach($lineas as $linea)
                        @php
                            $subtotal += floatval($linea->PRECIO_TOTAL ?? 0);
                        @endphp
                        <tr>
                            <td class="text-center">{{ number_format($linea->CANTIDAD, 0) }}</td>
                            <td class="text-center">{{ $linea->ARTICULO }}</td>
                            <td class="text-left">{{ $linea->DESCRIPCION }}</td>
                            <td class="text-center">-</td>
                            <td class="text-right">${{ number_format($linea->PRECIO_UNITARIO, 2) }}</td>
                            <td class="text-right">${{ number_format($linea->PRECIO_TOTAL, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- FOOTER -->
        <div class="footer-section">
            <!-- Left side: Boxes info and notes -->
            <div class="footer-left">
                <div class="footer-boxes">
                    <table>
                        <tr>
                            <td>Boxes/Cajas:</td>
                            <td>{{ $factura->CANTIDAD_BULTOS ?? '' }}</td>
                            <td># Container/Contenedor:</td>
                            <td>{{ $factura->CONTENEDOR ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Net Weight/Peso Neto:</td>
                            <td>{{ isset($factura->PESO_NETO) && $factura->PESO_NETO ? number_format($factura->PESO_NETO, 2) . ' Kg' : '' }}</td>
                            <td>Seals/Sellos:</td>
                            <td>{{ $factura->SELLOS ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Gross Weight/Peso Bruto:</td>
                            <td>{{ isset($factura->PESO_BRUTO) && $factura->PESO_BRUTO ? number_format($factura->PESO_BRUTO, 2) . ' Kg' : '' }}</td>
                            <td>Booking/Reserva BL:</td>
                            <td>{{ $factura->BOOKING ?? '' }}</td>
                        </tr>
                    </table>
                </div>

                <div class="footer-notes">
                    <strong>*Antes que ud haga su pago o transferencia, debe llamar antes e informar cuando realizara dicho pago
                    o transferencia indicandonos el monto, la cuenta a pagar, información sobre la transferencia que realizara en dicha cuenta.
                    SI EXIGE QUE SU BANCO ENVIE COMPROBANTE VIA E-MAIL A HYPLAST, DEBERA ENVIARNOS DICHA ORDEN A NUESTROS CORREOS:</strong><br><br>
                    <strong>BANCO BLANCO, AMERICAN DOLLAR - NO. DE CUENTA: 11590638-28</strong><br>
                    CURRENCY: USD/MONEDA: AMERICAN DOLLAR<br>
                    <strong>INVOICE IN PESOS DOMINICANO, TRANFER TO NACIONAL ACCOUNT - SAVINGS ACCOUNT / FACTURAS EN PESOS DOMINICANO</strong><br>
                    RD NACIONAL, TRASNFERIR A CUENTAS NACIONALES AHORROS<br>
                </div>
            </div>

            <!-- Right side: Totals -->
            <div class="footer-right">
                <table class="totals-table">
                    <tr>
                        <td>Sub-Total:</td>
                        <td>US$ {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Freight/Flete:</td>
                        <td>US$ {{ number_format($factura->MONTO_FLETE ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>VALOR TOTAL F.O.B. US$:</td>
                        <td>US$ {{ number_format($factura->MONTO_DOLAR ?? $subtotal, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- SIGNATURES -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">Dispatched By/<br>Despachado por:</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized By/<br>Autorizado por:</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Client Signature/<br>Firma de Cliente:</div>
            </div>
        </div>
    </div>


</body>
</html>
