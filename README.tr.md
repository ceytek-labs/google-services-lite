[🇬🇧 Click here for English version](README.md)

<p align="center">
    <a href="https://packagist.org/packages/ceytek-labs/google-services-lite">
        <img alt="Total Downloads" src="https://img.shields.io/packagist/dt/ceytek-labs/google-services-lite">
    </a>
    <a href="https://packagist.org/packages/ceytek-labs/google-services-lite">
        <img alt="Latest Version" src="https://img.shields.io/packagist/v/ceytek-labs/google-services-lite">
    </a>
    <a href="https://packagist.org/packages/ceytek-labs/google-services-lite">
        <img alt="Size" src="https://img.shields.io/github/repo-size/ceytek-labs/google-services-lite">
    </a>
    <a href="https://packagist.org/packages/ceytek-labs/google-services-lite">
        <img alt="License" src="https://img.shields.io/packagist/l/ceytek-labs/google-services-lite">
    </a>
</p>

# Google Services Lite - PHP için Google API Kullanımı

**Google Services Lite**, PHP projelerinizde Google Sheets API'si ile Google Sheets üzerinde kolayca veri yönetimi yapmanızı sağlayan hafif ve genişletilebilir bir kütüphanedir. Şu anda sadece Google Sheets API desteği sunmaktadır, ancak gelecekte YouTube ve diğer Google servisleri için de genişletilmesi planlanmaktadır.

## Kurulum

Bu paketi projelerinize eklemek için Composer kullanabilirsiniz:

```bash
composer require ceytek-labs/google-services-lite
```

## Gereklilikler

- PHP 7.0 veya daha üstü

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

**update:** Belirtilen Google Sheets sekmesindeki verileri sağlanan yeni verilerle günceller ve tüm mevcut verileri değiştirir. Güncellenen hücre sayısını döndürür.

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

**updateInChunks:** `update` ile benzer şekilde çalışır ancak büyük veri setlerini daha küçük parçalara ayırarak API limitlerini aşmadan işler. Güncellenen toplam hücre sayısını döndürür.

```php
use CeytekLabs\GoogleServicesLite\GoogleSheets;

$result = GoogleSheets::make('SPREADSHEET_ID')    // Google Sheets belgesinin kimliğini belirtin
    ->setCredentials(__DIR__.'/credentials.json') // Kimlik doğrulama dosyasını belirtin
    ->updateInChunks('Sheet1', [                  // Verileri küçük parçalara ayırarak güncelleyin
        ["Data 1", "Data 2", "Data 3"],           // Güncellenecek verileri ekleyin
        ["Data 4", "Data 5", "Data 6"],
        ["Data 7", "Data 8", "Data 9"],           // Büyük veri setleri durumunda veriler parçalara ayrılacak
    ], 50);                                       // Parça boyutunu (örneğin, 50 satır) belirleyin

echo 'Güncellenen hücre sayısı: ' . $result['updated_cells_count'];
```

**batchUpdate:** Google Sheets sekmesindeki belirli hücreleri toplu bir istekle günceller, her bir hücre üzerinde daha fazla kontrol sağlar. Güncelleme durumunu (başarı ya da başarısızlık) döndürür.

```php
use CeytekLabs\GoogleServicesLite\GoogleSheets;

$result = GoogleSheets::make('SPREADSHEET_ID')    // Google Sheets belgesinin kimliğini belirleyin
    ->setCredentials(__DIR__.'/credentials.json') // Kimlik doğrulama dosyasını ayarlayın
    ->batchUpdate('Sheet1', [                     // Verilerin güncelleneceği sekmenin adını belirleyin
        ["Veri 1", "Veri 2", "Veri 3"],           // Güncellenecek verileri ekleyin
        ["Veri 4", "Veri 5", "Veri 6"],
        ["Veri 7", "Veri 8", "Veri 9"],
    ]);

echo 'Toplu güncelleme durumu: ' . ($result['status'] ? 'Başarılı' : 'Başarısız');
```

**batchUpdateInChunks:** `batchUpdate` ile benzer şekilde çalışır ancak büyük veri setlerini daha küçük parçalara ayırarak API limitlerini aşmadan işler. Güncelleme durumunu (başarı ya da başarısızlık) döndürür.

```php
use CeytekLabs\GoogleServicesLite\GoogleSheets;

$result = GoogleSheets::make('SPREADSHEET_ID')    // Google Sheets belgesinin kimliğini belirleyin
    ->setCredentials(__DIR__.'/credentials.json') // Kimlik doğrulama dosyasını ayarlayın
    ->batchUpdateInChunks('Sheet1', [             // Verilerin güncelleneceği sekmenin adını belirleyin
        ["Veri 1", "Veri 2", "Veri 3"],           // Güncellenecek verileri ekleyin
        ["Veri 4", "Veri 5", "Veri 6"],
        ["Veri 7", "Veri 8", "Veri 9"],           // Büyük veri setleri durumunda veriler parçalara ayrılacak
    ], 750);                                      // Parça boyutunu (örneğin, 750 satır) belirleyin

echo 'Toplu güncelleme durumu: ' . ($result['status'] ? 'Başarılı' : 'Başarısız');
```

## Katkıda Bulunma

Katkıda bulunmak için bir **pull request** gönderebilir veya bir sorun bildirebilirsiniz. Her türlü katkı ve geri bildirim değerlidir!

## Lisans

Bu proje MIT Lisansı ile lisanslanmıştır.