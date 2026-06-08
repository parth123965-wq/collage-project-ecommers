INSERT INTO `Category` (`CategoryId`, `CategoryName`, `CategoryDescription`) VALUES
(1, 'Luxury Perfumes', 'Best-selling premium oriental perfumes and oud'),
(2, 'Abayas and Kaftans', 'Traditional clothing with elegant modern designs');

INSERT INTO `Product` (`productId`, `productName`, `productDescription`, `productPrice`, `productQuantity`, `productImage`, `CategoryId`) VALUES
(1, 'Royal Oud Oil', 'Premium natural oud fragrance with long-lasting scent for formal occasions.', 450.00, 15, 'oud.jpg', 1),
(2, 'White Musk Perfume', 'A captivating blend of pure white musk with warm notes.', 280.00, 22, 'musk.jpg', 1),
(3, 'Embroidered Velvet Abaya', 'Elegant winter abaya in royal black with handcrafted golden embroidery.', 650.00, 8, 'abaya.jpg', 2);