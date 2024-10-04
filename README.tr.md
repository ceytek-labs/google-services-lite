[🇬🇧 Click here for English version](README.md)

![License](https://img.shields.io/github/license/ceytek-labs/google-services-lite)
![SIZE](https://img.shields.io/github/repo-size/ceytek-labs/google-services-lite?label=size)

# Google Services Lite - PHP için Google API Kullanımı

**Google Services Lite**, PHP projelerinizde Google Sheets API'si ile Google Sheets üzerinde kolayca veri yönetimi yapmanızı sağlayan hafif ve genişletilebilir bir kütüphanedir. Şu anda sadece Google Sheets API desteği sunmaktadır, ancak gelecekte YouTube ve diğer Google servisleri için de genişletilmesi planlanmaktadır.

## Kurulum

Bu paketi projelerinize eklemek için Composer kullanabilirsiniz:

```bash
composer require ceytek-labs/google-services-lite
```

## Gereklilikler

- PHP 7.2 veya daha üstü

## Google Sheets API Kullanımı

Google Sheets API'sine erişmek için gerekli kimlik doğrulama dosyasını indirip projenizin kök dizinine eklemeniz gerekmektedir. Aşağıdaki adımlar size bu işlemi nasıl yapacağınızı gösterecektir:

- Google Cloud Console üzerinden bir proje oluşturun.
- Google Sheets API'yi etkinleştirin.
- "Service Account" türünde kimlik doğrulama bilgileri oluşturun.
- İndirdiğiniz kimlik doğrulama JSON dosyasını `credentials.json` adıyla projenizin kök dizinine kaydedin.
- Google Sheets üzerinde değişiklik yapabilmek için, bu kimlik doğrulama dosyasında yer alan servis hesabı e-posta adresini düzenleme izni ile paylaşın.
- Değişiklik yapacağınız Google Sheets dokümanının ID'sini ve sayfa adını not edin. Sayfa ID'si şu formatta bulunur:
    - `https://docs.google.com/spreadsheets/d/<id>`

## Örnek Kullanım

Aşağıdaki örnek, Google Sheets'teki bir sayfada nasıl veri güncelleyeceğinizi gösterir:

```php
use CeytekLabs\GoogleServicesLite\GoogleSheets;

$result = GoogleSheets::make('SPREADSHEET_ID')    // Google Sheets belgesinin kimliğini belirle
    ->setCredentials(__DIR__.'/credentials.json') // Kimlik doğrulama dosyasını ayarla
    ->update('Sheet1', [                          // Verilerin güncelleneceği sekmenin adını belirle
        ["Veri 1", "Veri 2", "Veri 3"],           // Güncellenecek verileri ekle
        ["Veri 4", "Veri 5", "Veri 6"],
        ["Veri 7", "Veri 8", "Veri 9"],
    ]);

echo 'Güncellenen hücre sayısı: ' . $result['updated_cells_count'];
```

```php
use CeytekLabs\GoogleServicesLite\GoogleSheets;

$result = GoogleSheets::make('SPREADSHEET_ID')    // Google Sheets belgesinin kimliğini belirtin
    ->setCredentials(__DIR__.'/credentials.json') // Kimlik doğrulama dosyasını belirtin
    ->updateInChunks('Sheet1', [                  // Verileri küçük parçalara ayırarak güncelleyin
        ["Data 1", "Data 2", "Data 3"],           // Güncellenecek verileri ekleyin
        ["Data 4", "Data 5", "Data 6"],
        ["Data 7", "Data 8", "Data 9"],           // Büyük veri setleri durumunda veriler parçalara ayrılacak
    ], 500);                                      // Parça boyutunu (örneğin, 500 satır) belirleyin

echo 'Güncellenen hücre sayısı: ' . $result['updated_cells_count'];
```

## Katkıda Bulunma

Katkıda bulunmak için bir **pull request** gönderebilir veya bir sorun bildirebilirsiniz. Her türlü katkı ve geri bildirim değerlidir!

## Lisans

Bu proje MIT Lisansı ile lisanslanmıştır.