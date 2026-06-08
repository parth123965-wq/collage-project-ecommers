INSERT INTO `Category` (`CategoryId`, `CategoryName`, `CategoryDescription`) VALUES
(1, 'عطور فاخرة', 'أرقى العطور الشرقية والعود مبيعاً'),
(2, 'عبايات وقفاطين', 'ملابس تراثية بتصاميم عصرية أنيقة');

INSERT INTO `Product` (`productId`, `productName`, `productDescription`, `productPrice`, `productQuantity`, `productImage`, `CategoryId`) VALUES
(1, 'دهن العود الملكي', 'عطر عود طبيعي فاخر يدوم طويلاً للمناسبات الرسمية.', 450.00, 15, 'oud.jpg', 1),
(2, 'عطر مسك الغزال', 'مزيج ساحر من المسك الأبيض النقي مع نفحات دافئة.', 280.00, 22, 'musk.jpg', 1),
(3, 'عباية مخملية مطرزة', 'عباية شتوية راقية باللون الأسود الملكي مع تطريز ذهبي يدوي.', 650.00, 8, 'abaya.jpg', 2);