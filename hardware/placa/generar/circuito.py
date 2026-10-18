# -*- coding: utf-8 -*-
"""
circuito.py — La placa portadora de WTTC descrita como datos: piezas, valores, huellas, códigos LCSC (JLCPCB) y a qué
red va cada pin. De aquí salen el esquema (esquema.py) y la placa (pcb.py). Generado con Claude (Anthropic).

Qué lleva (ver ../README.md):
  - Entrada del vehículo por borna de 3 tornillos: +12 V permanente, masa y W-Bus (lo del conector del temporizador).
  - Protecciones: fusible rearmable 0,5 A, diodo contra polaridad inversa (SS34) y supresor de picos (SMBJ24A, que
    recorta por debajo de los 40 V que aguanta el TJA1021).
  - 5 V con un conmutado LMR16006X (4–60 V, 0,6 A, 28 µA en reposo): el ejemplo de 5 V de la hoja de datos de TI con
    56 kΩ / 10 kΩ (5,05 V).
  - Transceptor W-Bus TJA1021T/20 (sustituto del TJA1020 de los módulos, mismo patillaje) con su resistencia de nodo
    maestro (1 kΩ y diodo, que se quita cortando el puente JP1) y 1 nF en el bus.
  - Zócalos para la ESP32-S3-DevKitC-1 (2 × 22 pines, filas a 25,4 mm: la huella de Espressif dice 22,86 y está mal).
  - Conectores de las piezas opcionales: I2C (Qwiic y dos tiras de 4 pines: pantalla y termómetro), sensor de CO
    ZE07-CO, botón «calentar» (pulsador en la placa y tira de 2 para uno externo).

Los pines del ESP32 son los del firmware (firmware/WTTC/WTTC.ino): W-Bus RX IO16 / TX IO17, I2C SDA IO4 / SCL IO5,
CO RX IO15 (TXD del sensor) / TX IO6, botón IO7.
"""

# Pines de las dos filas de la ESP32-S3-DevKitC-1 (documentación de Espressif). J_ESP_A: la fila de 3V3 (la «J1» de
# Espressif); J_ESP_B: la de GND/TX/RX (la «J3»). El pin 1 de las dos está en el lado de la antena.
FILA_A = ["3V3", "3V3", "RST", "IO4", "IO5", "IO6", "IO7", "IO15", "IO16", "IO17", "IO18", "IO8", "IO3", "IO46", "IO9",
          "IO10", "IO11", "IO12", "IO13", "IO14", "5V", "GND"]
FILA_B = ["GND", "IO43", "IO44", "IO1", "IO2", "IO42", "IO41", "IO40", "IO39", "IO38", "IO37", "IO36", "IO35", "IO0",
          "IO45", "IO48", "IO47", "IO21", "IO20", "IO19", "GND", "GND"]
USO_ESP = {"3V3": "+3V3", "5V": "+5V", "GND": "GND", "IO4": "SDA", "IO5": "SCL", "IO6": "CO_RX", "IO7": "BOTON",
           "IO15": "CO_TX", "IO16": "WBUS_RX", "IO17": "WBUS_TX"}


def fila(nombres):
    return {str(i + 1): USO_ESP.get(n) for i, n in enumerate(nombres)}


# Cada pieza: referencia, símbolo (biblioteca:nombre), valor, huella, LCSC ("" = se suelda a mano o no se monta),
# pines {número: red o None (sin conectar)}, bloque del esquema y descripción para la lista de materiales.
# Notas de montaje: "smd" lo monta JLCPCB; "tht" (bornas, zócalos, tiras) se suelda a mano en dos minutos.
PIEZAS = [
    # ---------------- Entrada del vehículo y protecciones ----------------
    dict(ref="J1", sym="Connector_Generic:Conn_01x03", val="Vehículo: 12V GND WBUS",
         fp="TerminalBlock_Phoenix:TerminalBlock_Phoenix_MKDS-1,5-3-5.08_1x03_P5.08mm_Horizontal",
         lcsc="C474953", mpn="KF128-5.08-3P", montaje="tht", bloque="entrada",
         pines={"1": "+12V_IN", "2": "GND", "3": "WBUS"}, desc="Borna de 3 tornillos, paso 5,08 mm"),
    dict(ref="F1", sym="Device:Polyfuse", val="0,5 A 60 V", fp="Fuse:Fuse_1812_4532Metric", lcsc="C70113",
         mpn="mSMD050-60V", montaje="smd", bloque="entrada", pines={"1": "+12V_IN", "2": "+12V_F"},
         desc="Fusible rearmable 1812, 0,5 A, 60 V"),
    dict(ref="D1", sym="Device:D_Schottky", val="SS34", fp="Diode_SMD:D_SMA", lcsc="C8678", mpn="SS34", montaje="smd",
         bloque="entrada", pines={"1": "+12V", "2": "+12V_F"}, desc="Schottky 40 V 3 A: protección contra polaridad inversa"),
    dict(ref="D2", sym="Device:D_Zener", val="SMBJ24A", fp="Diode_SMD:D_SMB", lcsc="C908801", mpn="SMBJ24A",
         montaje="smd", bloque="entrada", pines={"1": "+12V", "2": "GND"},
         desc="Supresor de picos 24 V (recorta a 38,9 V)"),
    dict(ref="C1", sym="Device:C", val="10µF 50V", fp="Capacitor_SMD:C_1206_3216Metric", lcsc="C13585",
         mpn="CL31A106KBHNNNE", montaje="smd", bloque="entrada", pines={"1": "+12V", "2": "GND"}, desc="Cerámico X5R 1206"),
    dict(ref="C2", sym="Device:C", val="100nF 50V", fp="Capacitor_SMD:C_0603_1608Metric", lcsc="C14663",
         mpn="CC0603KRX7R9BB104", montaje="smd", bloque="entrada", pines={"1": "+12V", "2": "GND"}, desc="Cerámico X7R 0603"),

    # ---------------- 5 V: LMR16006X (ejemplo de 5 V de la hoja de datos) ----------------
    dict(ref="U2", sym="Regulator_Switching:LMR16006YQ", val="LMR16006XDDCR", fp="Package_TO_SOT_SMD:SOT-23-6",
         lcsc="C87080", mpn="LMR16006XDDCR", montaje="smd", bloque="5v",
         pines={"1": "CB", "2": "GND", "3": "FB", "4": "SHDN", "5": "+12V", "6": "SW"},
         desc="Regulador conmutado 4–60 V, 0,6 A, 700 kHz"),
    dict(ref="R1", sym="Device:R", val="100k", fp="Resistor_SMD:R_0603_1608Metric", lcsc="C25803", mpn="0603WAF1003T5E",
         montaje="smd", bloque="5v", pines={"1": "+12V", "2": "SHDN"}, desc="Habilitación del regulador"),
    dict(ref="C3", sym="Device:C", val="2.2µF 50V", fp="Capacitor_SMD:C_1206_3216Metric", lcsc="C50254",
         mpn="CL31B225KBHNNNE", montaje="smd", bloque="5v", pines={"1": "+12V", "2": "GND"}, desc="Entrada del regulador, X7R"),
    dict(ref="C4", sym="Device:C", val="100nF 50V", fp="Capacitor_SMD:C_0603_1608Metric", lcsc="C14663",
         mpn="CC0603KRX7R9BB104", montaje="smd", bloque="5v", pines={"1": "CB", "2": "SW"}, desc="Condensador de arranque (CB–SW)"),
    dict(ref="L1", sym="Device:L", val="22µH", fp="Inductor_SMD:L_Sunlord_SWPA4030S", lcsc="C83472",
         mpn="SWPA4030S220MT", montaje="smd", bloque="5v", pines={"1": "SW", "2": "+5V"}, desc="Bobina 22 µH, 1 A, 4×4 mm"),
    dict(ref="D3", sym="Device:D_Schottky", val="SS16", fp="Diode_SMD:D_SMA", lcsc="C2481", mpn="SS16", montaje="smd",
         bloque="5v", pines={"1": "SW", "2": "GND"}, desc="Schottky 60 V 1 A (diodo del conmutado)"),
    dict(ref="R2", sym="Device:R", val="56k", fp="Resistor_SMD:R_0603_1608Metric", lcsc="C23206", mpn="0603WAF5602T5E",
         montaje="smd", bloque="5v", pines={"1": "+5V", "2": "FB"}, desc="Divisor de realimentación (arriba): 5,05 V"),
    dict(ref="R3", sym="Device:R", val="10k", fp="Resistor_SMD:R_0603_1608Metric", lcsc="C25804", mpn="0603WAF1002T5E",
         montaje="smd", bloque="5v", pines={"1": "FB", "2": "GND"}, desc="Divisor de realimentación (abajo)"),
    dict(ref="C5", sym="Device:C", val="22µF 25V", fp="Capacitor_SMD:C_0805_2012Metric", lcsc="C45783",
         mpn="CL21A226MAQNNNE", montaje="smd", bloque="5v", pines={"1": "+5V", "2": "GND"}, desc="Salida de 5 V, X5R"),
    dict(ref="C6", sym="Device:C", val="100nF 50V", fp="Capacitor_SMD:C_0603_1608Metric", lcsc="C14663",
         mpn="CC0603KRX7R9BB104", montaje="smd", bloque="5v", pines={"1": "+5V", "2": "GND"}, desc="Salida de 5 V"),

    # ---------------- W-Bus: TJA1021T/20 ----------------
    dict(ref="U1", sym="wttc-placa:TJA1021T", val="TJA1021T/20", fp="Package_SO:SOIC-8_3.9x4.9mm_P1.27mm",
         lcsc="C128810", mpn="TJA1021T/20/CM,118", montaje="smd", bloque="wbus",
         pines={"1": "WBUS_RX", "2": "+3V3", "3": "WAKE", "4": "WBUS_TX", "5": "GND", "6": "WBUS", "7": "+12V", "8": None},
         desc="Transceptor LIN/K-Line (W-Bus); SLP_N fijo a 3V3 como en el montaje con módulo"),
    dict(ref="R4", sym="Device:R", val="10k", fp="Resistor_SMD:R_0603_1608Metric", lcsc="C25804", mpn="0603WAF1002T5E",
         montaje="smd", bloque="wbus", pines={"1": "+12V", "2": "WAKE"}, desc="WAKE_N sin usar: a batería"),
    dict(ref="R5", sym="Device:R", val="4.7k", fp="Resistor_SMD:R_0603_1608Metric", lcsc="C23162", mpn="0603WAF4701T5E",
         montaje="smd", bloque="wbus", pines={"1": "+3V3", "2": "WBUS_RX"}, desc="RXD es de drenador abierto: resistencia a 3V3"),
    dict(ref="C7", sym="Device:C", val="100nF 50V", fp="Capacitor_SMD:C_0603_1608Metric", lcsc="C14663",
         mpn="CC0603KRX7R9BB104", montaje="smd", bloque="wbus", pines={"1": "+12V", "2": "GND"}, desc="Desacoplo de VBAT"),
    dict(ref="C8", sym="Device:C", val="1nF 50V", fp="Capacitor_SMD:C_0603_1608Metric", lcsc="C1588",
         mpn="CL10B102KB8NNNC", montaje="smd", bloque="wbus", pines={"1": "WBUS", "2": "GND"}, desc="Condensador del bus (nodo maestro)"),
    dict(ref="D4", sym="Device:D", val="1N4148W", fp="Diode_SMD:D_SOD-123", lcsc="C81598", mpn="1N4148W", montaje="smd",
         bloque="wbus", pines={"1": "MAESTRO_K", "2": "+12V"}, desc="Diodo de la resistencia de maestro"),
    dict(ref="R6", sym="Device:R", val="1k", fp="Resistor_SMD:R_0603_1608Metric", lcsc="C21190", mpn="0603WAF1001T5E",
         montaje="smd", bloque="wbus", pines={"1": "MAESTRO_K", "2": "MAESTRO_R"}, desc="Resistencia de nodo maestro del W-Bus"),
    dict(ref="JP1", sym="Jumper:SolderJumper_2_Bridged", val="MAESTRO",
         fp="Jumper:SolderJumper-2_P1.3mm_Bridged_Pad1.0x1.5mm", lcsc="", mpn="", montaje="pcb", bloque="wbus",
         pines={"1": "MAESTRO_R", "2": "WBUS"}, desc="Puente de cobre: cortarlo quita la resistencia de maestro"),

    # ---------------- ESP32-S3-DevKitC-1 ----------------
    dict(ref="J2", sym="Connector_Generic:Conn_01x22", val="ESP32-S3 (fila 3V3)",
         fp="Connector_PinSocket_2.54mm:PinSocket_1x22_P2.54mm_Vertical", lcsc="C41376161", mpn="HC-PM254-8.5H-1x22PZ",
         montaje="tht", bloque="esp", pines=fila(FILA_A), desc="Zócalo hembra 1×22, 2,54 mm"),
    dict(ref="J3", sym="Connector_Generic:Conn_01x22", val="ESP32-S3 (fila GND/TX/RX)",
         fp="Connector_PinSocket_2.54mm:PinSocket_1x22_P2.54mm_Vertical", lcsc="C41376161", mpn="HC-PM254-8.5H-1x22PZ",
         montaje="tht", bloque="esp", pines=fila(FILA_B), desc="Zócalo hembra 1×22, 2,54 mm"),

    # ---------------- Piezas opcionales ----------------
    dict(ref="J4", sym="Connector_Generic:Conn_01x04", val="I2C Qwiic",
         fp="Connector_JST:JST_SH_SM04B-SRSS-TB_1x04-1MP_P1.00mm_Horizontal", lcsc="C160404",
         mpn="SM04B-SRSS-TB(LF)(SN)", montaje="smd", bloque="opc",
         pines={"1": "GND", "2": "+3V3", "3": "SDA", "4": "SCL"}, desc="I2C con conector Qwiic/STEMMA QT (GND 3V3 SDA SCL)"),
    dict(ref="J5", sym="Connector_Generic:Conn_01x04", val="Pantalla: GND 3V3 SCL SDA",
         fp="Connector_PinHeader_2.54mm:PinHeader_1x04_P2.54mm_Vertical", lcsc="", mpn="Tira macho 1×4", montaje="tht",
         bloque="opc", pines={"1": "GND", "2": "+3V3", "3": "SCL", "4": "SDA"},
         desc="Pantalla OLED I2C (orden de las de 0,96\"/1,3\")"),
    dict(ref="J6", sym="Connector_Generic:Conn_01x04", val="Termómetro: 3V3 GND SCL SDA",
         fp="Connector_PinHeader_2.54mm:PinHeader_1x04_P2.54mm_Vertical", lcsc="", mpn="Tira macho 1×4", montaje="tht",
         bloque="opc", pines={"1": "+3V3", "2": "GND", "3": "SCL", "4": "SDA"}, desc="SHT31 / AHT20 (VIN GND SCL SDA)"),
    dict(ref="J7", sym="Connector_Generic:Conn_01x04", val="Sensor CO: 5V GND TXD RXD",
         fp="Connector_PinHeader_2.54mm:PinHeader_1x04_P2.54mm_Vertical", lcsc="", mpn="Tira macho 1×4", montaje="tht",
         bloque="opc", pines={"1": "+5V", "2": "GND", "3": "CO_TX", "4": "CO_RX"},
         desc="ZE07-CO: VIN 5 V, GND, su TXD → IO15, su RXD ← IO6"),
    dict(ref="J8", sym="Connector_Generic:Conn_01x02", val="Botón externo",
         fp="Connector_PinHeader_2.54mm:PinHeader_1x02_P2.54mm_Vertical", lcsc="", mpn="Tira macho 1×2", montaje="tht",
         bloque="opc", pines={"1": "BOTON", "2": "GND"}, desc="Pulsador «calentar» externo (IO7 a masa)"),
    dict(ref="SW1", sym="Switch:SW_Push", val="Calentar", fp="Button_Switch_SMD:SW_Push_1P1T_XKB_TS-1187A",
         lcsc="C318884", mpn="TS-1187A-B-A-B", montaje="smd", bloque="opc", pines={"1": "BOTON", "2": "GND"},
         desc="Pulsador «calentar» en la placa"),

    # ---------------- Taladros ----------------
] + [dict(ref=f"H{i}", sym="Mechanical:MountingHole", val="M3", fp="MountingHole:MountingHole_3.2mm_M3_Pad", lcsc="",
          mpn="", montaje="pcb", bloque="mec", pines={}, desc="Taladro M3 metalizado, a masa") for i in range(1, 5)]

# Redes de alimentación: pistas más anchas (pcb.py) y marca de «alimentada» para la comprobación del esquema
POTENCIA = ["+12V_IN", "+12V_F", "+12V", "+5V", "GND", "SW"]
ALIMENTADAS = ["+12V", "+5V", "+3V3", "GND"]   # +3V3 la pone el regulador de la propia DevKit
