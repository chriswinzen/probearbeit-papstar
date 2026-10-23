# PIM ETL Middleware
## notizen
Ich habe mich für die Verarbeitung eines Arrays entschieden, da die Daten nicht in eine DB geschrieben werden.
  So können die Daten leicht erweitert werden & 1:1 durchgeschliffen werden. Das kann im späteren Verlauf zu Problemen
  führen, da beim Mergen von Daten bereits existierende Properties überschrieben werden können. Dies soll aber der
  Definition der PIM ETL Middleware nicht im Wege stehen.

## ETL-Struktur für den SKU-Datenimport

- Import Datei: `artikel.csv`
- Export Datei: `ausgabe.json`
- Error Log: `errors.json`
- Aufruf: `php import.php artikel.csv ausgabe.json`
- Spalten: `sku`, `name`, `preis`, `bestand`, `kategorie`, `ean`

### 1. Extract – Daten einlesen

**Ziel:** Die Quelldatei einlesen und die Datensätze für die Verarbeitung vorbereiten.

- Quelldatei einlesen und alle Datensätze erfassen.
- Die Kopfzeile als Zeile 1 behandeln.
- Für jeden Datensatz die ursprüngliche Zeilennummer ermitteln: `Index + 2` (nullbasierter Index, Kopfzeile bereits berücksichtigt).
- Die Anzahl der gelesenen Datensätze erfassen.

### 2. Transform – Daten validieren und aufbereiten

**Ziel:** Ungültige Datensätze erkennen, Daten vereinheitlichen und doppelte SKUs bereinigen.

#### 2.1 Validierung

- **SKU:** Datensätze ohne SKU werden übersprungen.
- **Preis:** Datensätze mit einem nicht auswertbaren Preis werden übersprungen.
- **Bestand:** Datensätze mit einem nicht auswertbaren Bestand werden übersprungen.
- **Bestand:** Negative Werte sind ungültig.
- **Bestand:** Der Wert `null` ist für nicht physische Produkte oder Zubehör erlaubt.
- **Fehlerprotokoll:** Jeder fehlerhafte Datensatz wird mit Zeilennummer und Fehlergrund protokolliert.

#### 2.2 Preisformat vereinheitlichen

- Unterschiedliche Preisformate werden in eine Zahl mit zwei Nachkommastellen umgewandelt.
- Für den Wert "kostenlos" wird der Preis auf `0.00` gesetzt.
- Nicht auswertbare Werte führen zu einer Exception.

| Eingabewert | Ergebnis |
|---|---:|
| `12,90` | `12.90` |
| `12.90` | `12.90` |
| `12,90 €` | `12.90` |
| `9,5` | `9.50` |
| `kostenlos` | `0.00` |

#### 2.3 Doppelte SKUs bereinigen

- Datensätze mit derselben SKU werden zusammengeführt.
- Der letzte gültige Datensatz einer SKU gewinnt.
- Frühere gültige Datensätze derselben SKU werden geskippt.

#### 2.4 Sortierung vorbereiten

SKU ASC

### 3. Load – Daten ausgeben

**Ziel:** Die aufbereiteten Daten als JSON-Datei bereitstellen.

- Ausgabe als gültiges JSON-Array.
- Die Validierung der einzelnen Datensätze & Duplikate findet im Transformer statt.

#### 3.2 Beispiel für das Fehlerprotokoll

```json
[
  {
    "line": 5,
    "error": "SKU is invalid: \"\""
  },
  {
    "line": 7,
    "error": "Quantity is invalid: \"-5\""
  },
  {
    "line": 9,
    "error": "Price is invalid: \"\""
  }
]
```
