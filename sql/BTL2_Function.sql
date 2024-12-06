-- =============================================
-- Author:      huukha.04
-- Create Date: //2024
-- Description: 
-- =============================================
GO
DROP FUNCTION IF EXISTS CalculateProductRating;
GO
CREATE FUNCTION CalculateProductRating(@product_id INT)
RETURNS INT
AS
BEGIN
    DECLARE @total_rating INT = 0;
    DECLARE @rating_count INT = 0;
    DECLARE @avg_rating DECIMAL(3, 2);
    DECLARE @rounded_rating INT;

    -- Tính tổng điểm và số lượng đánh giá của sản phẩm
    SELECT @total_rating = SUM(r.rating), @rating_count = COUNT(r.rating)
    FROM Reviews r
    JOIN OrderDetails od ON r.order_id = od.order_id AND r.orderDetail_id = od.orderDetail_id
    WHERE od.product_id = @product_id;

    -- Tính trung bình và làm tròn
    SET @avg_rating = CASE WHEN @rating_count > 0 THEN @total_rating * 1.0 / @rating_count ELSE 0 END;
    SET @rounded_rating = ROUND(@avg_rating, 0);

    -- Đảm bảo giá trị trả về trong khoảng từ 0 đến 5
    IF @rounded_rating < 0
        SET @rounded_rating = 0;
    ELSE IF @rounded_rating > 5
        SET @rounded_rating = 5;

    RETURN @rounded_rating;
END;
GO


SELECT dbo.CalculateProductRating(102) AS ProductRating;


-- =============================================
-- Author:      huukha.04
-- Create Date: //2024
-- Description: 
-- =============================================
-- =============================================
-- Author:      huukha.04
-- Create Date: //2024
-- Description: 
-- =============================================
GO
DROP FUNCTION IF EXISTS CalculateTotalRevenueByOrder;
GO
CREATE FUNCTION CalculateTotalRevenueByOrder (@orderId INT)
RETURNS DECIMAL(10, 2)
AS
BEGIN
    -- Kiểm tra tham số đầu vào (ID của Order)
    IF @orderId IS NULL OR @orderId <= 0 
    BEGIN
        RETURN 0.0;  -- Nếu orderId không hợp lệ, trả về 0
    END

    DECLARE @totalRevenue DECIMAL(10, 2) = 0.0;
    DECLARE @revenue DECIMAL(10, 2);
    DECLARE @voucher_id INT;
    DECLARE @discount_amount DECIMAL(10, 2) = 0.0;
    DECLARE @shipping_fee INT = 0;

    -- Lấy voucher_id của đơn hàng (nếu có)
    SELECT @voucher_id = voucher_id
    FROM Orders
    WHERE order_id = @orderId;

    -- Nếu có voucher, lấy discount_amount từ bảng Vouchers
    IF @voucher_id IS NOT NULL
    BEGIN
        SELECT @discount_amount = discount_amount
        FROM Vouchers
        WHERE voucher_id = @voucher_id;
    END

    -- Lấy shipping_fee từ bảng Shipping
    SELECT @shipping_fee = shipping_fee
    FROM Shipping
    WHERE order_id = @orderId;

    -- Khai báo con trỏ để duyệt qua các bản ghi trong OrderDetails cho order_id
    DECLARE cur CURSOR FOR
        SELECT od.price_on_purchase * od.quantity
        FROM OrderDetails od
        JOIN Products p ON od.product_id = p.product_id
        WHERE od.order_id = @orderId;

    OPEN cur;

    -- Lặp qua các bản ghi từ con trỏ để tính tổng doanh thu
    FETCH NEXT FROM cur INTO @revenue;
    WHILE @@FETCH_STATUS = 0
    BEGIN
        -- Cộng dồn doanh thu
        SET @totalRevenue = @totalRevenue + @revenue;
        
        -- Lấy bản ghi tiếp theo
        FETCH NEXT FROM cur INTO @revenue;
    END

    -- Đóng con trỏ và giải phóng tài nguyên
    CLOSE cur;
    DEALLOCATE cur;

    -- Trừ đi discount_amount và cộng vào shipping_fee
    SET @totalRevenue = @totalRevenue - ISNULL(@discount_amount, 0.0) + ISNULL(@shipping_fee, 0);

    -- Trả về tổng doanh thu
    RETURN @totalRevenue;                 
END;
GO





--DATA MẪU
--------6 buyer, 2 seller-------------
----seller la nguyen thi d và tran thi h
insert into Users (last_name, first_name, email, password, phone_number)
values
('Pham', 'Van I', 'phami@gmail.com', '123456', '0376812319'),
('Pham', 'Van J', 'phamj@gmail.com', '123456', '0376812320'),
('Pham', 'Thi K', 'phamk@gmail.com', '123456', '0376812321'),
('Pham', 'Thi L', 'phaml@gmail.com', '123456', '0376812322'),
('Le', 'Van M', 'lem@gmail.com', '123456', '0376812323'),
('Le', 'Van N', 'len@gmail.com', '123456', '0376812324'),
('Le', 'Thi O', 'leo@gmail.com', '123456', '0376812325'),
('Le', 'Thi P', 'lep@gmail.com', '123456', '0376812326'),
('Hoang', 'Van Q', 'hoangq@gmail.com', '123456', '0376812327'),
('Hoang', 'Van R', 'hoangr@gmail.com', '123456', '0376812328'),
('Hoang', 'Thi S', 'hoangs@gmail.com', '123456', '0376812329'),
('Hoang', 'Thi T', 'hoangt@gmail.com', '123456', '0376812330'),
('Vo', 'Van U', 'vou@gmail.com', '123456', '0376812331'),
('Vo', 'Van V', 'vov@gmail.com', '123456', '0376812332'),
('Vo', 'Thi W', 'vow@gmail.com', '123456', '0376812333'),
('Vo', 'Thi X', 'vox@gmail.com', '123456', '0376812334'),
('Ngo', 'Van Y', 'ngoy@gmail.com', '123456', '0376812335'),
('Ngo', 'Van Z', 'ngoz@gmail.com', '123456', '0376812336'),
('Ngo', 'Thi AA', 'ngoaa@gmail.com', '123456', '0376812337'),
('Ngo', 'Thi BB', 'ngobb@gmail.com', '123456', '0376812338');

----------------------------------------------------------------------
insert into ShippingAddress (user_id, address) values
(9, '33 Le Minh Xuan, Phuong 7, Tan Binh, Ho Chi Minh'),
(9, '110A Banh Van Tran, Phuong 7, Tan Binh, Ho Chi Minh'),
(10, 'Hem 766A Lac Long Quan, Phuong 9, Tan Binh, Ho Chi Minh'),
(10, '290 Ba Trac, Phuong 1, Quan 8, Ho Chi Minh'),
(11, '147 Nguyen Tat Thanh, Phuong 13, Quan 4, Ho Chi Minh'),
(11, '103 Ton Dan, Phuong 14, Quan 4, Ho Chi Minh'),
(13, '32 Tan My, Tan Thuan Tay, Quan 7, Ho Chi Minh'),
(13, '21 Lam Van Ben, Tan Kieng, Quan 7, Ho Chi Minh'),
(14, '122 Lam Van Ben, Binh Thuan, Quan 7, Ho Chi Minh'),
(14, '40 Ly Phuc Man, Binh Thuan, Quan 7, Ho Chi Minh'),
(15, '230 Le Duc Tho, Phuong 6, Go Vap, Ho Chi Minh'),
(15, '235 Nguyen Oanh, Phuong 17, Go Vap, Ho Chi Minh')
----------------------------------------------------------------------
insert into Buyers (buyer_id, payment_recommended, introduced_buyer) values
(9, 'bank transfer', null),
(10, 'cash', 9),
(11, 'cash', 9),
(13, 'cash', 11),
(14, 'bank transfer', null),
(15, 'bank transfer', 14)
----------------------------------------------------------------------
insert into Sellers (seller_id, shop_name) values
(12, '4MEN'), 
(16, 'Daisy')
----------------------------------------------------------------------
insert into Vouchers (seller_id, voucher_code, start_date, expiry_date, discount_amount, condition, limit_per_number, total_usage)
values
(12, 'VC001', '2024-11-20', '2024-11-26', 50000, 300000, 2, 20),
(12, 'VC002', '2024-11-25', '2024-12-20', 50000, 400000, 1, 10),
(16, 'VC003', '2024-11-25', '2024-12-20', 20000, 200000, 3, 30),
(16, 'VC004', '2024-11-25', '2024-12-20', 70000, 500000, 2, 20)
----------------------------------------------------------------------
insert into Products (seller_id, product_name, price, description, category) 
values
(12, 'Cotton T-Shirt',  180000,  'Lightweight, breathable t-shirt for casual everyday wear', 'upperwear'),
(12, 'Classic Oxford Shirt', 280000, 'Timeless oxford shirt made with soft cotton fabric, perfect for formal or casual wear', 'upperwear'),
(16, 'Polo Shirt', 200000, 'Classic fit polo shirt with a ribbed collar, perfect for smart-casual outfits', 'upperwear'),
(16, 'Hooded Sweatshirt', 150000, 'Warm and cozy hoodie with a kangaroo pocket, ideal for cooler weather', 'upperwear'),
(12, 'Chino Pants', 300000, 'Lightweight cotton chinos, suitable for both casual and semi-formal wear.', 'lowerwear'),
(12, 'Baggy denim Bermuda shorts', 220000, 'Baggy denim Bermuda shorts with a five-pocket design, waistband with belt loops, zip fly and top button fastening. Made of cotton', 'lowerwear'),
(16, 'Tailored Suit Pants', 350000, 'Elegant tailored pants designed for formal occasions or business attire', 'lowerwear'),
(16, 'Cargo Bermuda shorts', 250000, 'Cargo Bermuda Shorts are knee-length, durable shorts with multiple pockets, perfect for casual or outdoor activities', 'lowerwear')
------------------------------------------------------------------------
insert into ProductVariant (id, product_id, color, size, quantity) values
(1, 100, 'white', 'L', 10),
(2, 100, 'white', 'XL', 10),
(3, 100, 'black', 'L', 10),
(4, 100, 'black', 'XL', 10),
(1, 101, 'white', 'L', 5),
(2, 101, 'white', 'XL', 5),
(3, 101, 'light blue', 'L', 5),
(4, 101, 'light blue', 'XL', 5),
(1, 102, 'navy blue', 'L', 6),
(2, 102, 'navy blue', 'XL', 6),
(3, 102, 'gray', 'L', 6),
(4, 102, 'gray', 'XL', 6),
(1, 103, 'black', 'L', 7),
(2, 103, 'black', 'XL', 7),
(3, 103, 'red', 'L', 7),
(4, 103, 'red', 'XL', 7),
(1, 104, 'khaki', '38', 8),
(2, 104, 'khaki', '40', 8),
(3, 104, 'navy blue', '38', 8),
(4, 104, 'navy blue', '40', 8),
(1, 105, 'neon blue', '38', 4),
(2, 105, 'neon blue', '40', 4),
(3, 105, 'pale indigo', '38', 4),
(4, 105, 'pale indigo', '40', 4),
(1, 106, 'black', '30', 15),
(2, 106, 'black', '31', 15),
(3, 106, 'beige', '30', 15),
(4, 106, 'beige', '31', 15),
(1, 107, 'brown', 'M', 20),
(2, 107, 'brown', 'L', 20),
(3, 107, 'pale khaki', 'M', 20),
(4, 107, 'pale khaki', 'L', 20)
------------------------------------------------------------------------
insert into UpperWear (upper_id, neck_type, sleeve_length) values
(100, 'crew neck', 'short sleeve'),
(101, 'button-down', 'long sleeve'),
(102, 'polo neck', 'short sleeve'),
(103, 'hoodie', 'long sleeve')
------------------------------------------------------------------------
insert into LowerWear(lower_id, waist_style, leg_length) values
(104, 'high waist', 'ankle length'),
(105, 'low waist', 'knee length'),
(106, 'high waist', 'full length'),
(107, 'mid waist', 'knee length')
------------------------------------------------------------------------
insert into LayeringStyle (style_name, season) values
('smart casual', 'autumn'),
('casual streetwear', 'summer'),
('casual streetwear', 'winter')
------------------------------------------------------------------------
insert into FitType (fittype_name, target_audience) values
('regular fit', 'office worker'),
('straingt fit', 'people with a balanced or moderate body shape'),
('relax fit', 'people like comfort, not constraints.')
------------------------------------------------------------------------
insert into UpperLayering (upper_id, style_id) values
(100, 202),
(101, 200),
(102, 200),
(102, 204),
(103, 204)
------------------------------------------------------------------------
insert into LowerFit (lower_id, fit_id) values
(104, 250),
(104, 252),
(105, 254),
(106, 250),
(106, 252),
(107, 254)
------------------------------------------------------------------------
insert into Orders (buyer_id) values
(9), (10), (11), (13), (14), (15)
------------------------------------------------------------------------
insert into OrderDetails (order_id, orderDetail_id, product_id, pv_stt, price_on_purchase, quantity) values
(300, 1, 100, 1, 180000, 2),
(302, 1, 101, 2, 280000, 1),
(302, 2, 106, 2, 350000, 1),
(304, 1, 102, 3, 200000, 1),
(304, 2, 107, 2, 250000, 1),
(306, 1, 103, 3, 150000, 1),
(308, 1, 103, 2, 150000, 1),
(308, 2, 105, 1, 220000, 1),
(310, 1, 104, 4, 300000, 2)
-----------------------------------------------------------------
INSERT INTO Payment (order_id, ref_code, payment_method, status) VALUES
(300, 'P1000', 'bank transfer', 'Pending'),
(302, NULL, 'cash', 'Pending'),
(304, NULL, 'cash', 'Pending'),
(306, NULL, 'cash', 'Pending'),
(308, 'P1008', 'bank transfer', 'Completed'),
(310, 'P1010', 'bank transfer', 'Completed');


insert into Shipping (order_id, tracking_number, shipping_address, shipping_partner, driver_name, shipping_fee) values
(300, 'S1000', '33 Le Minh Xuan, Phuong 7, Tan Binh, Ho Chi Minh', 'VNPost', 'Nguyen Van V', 30000),
(302, 'S1002', '290 Ba Trac, Phuong 1, Quan 8, Ho Chi Minh', 'Viettel Post', 'Nguyen Van U', 20000),
(304, 'S1004', '147 Nguyen Tat Thanh, Phuong 13, Quan 4, Ho Chi Minh', 'J&T Express', 'To Van L', 25000)




insert into  SaveVoucher(buyer_id ,voucher_id) values
(9,1000),
(10,1002),
(11,1004)

INSERT INTO Reviews (order_id, orderDetail_id, comment, rating) VALUES
(300, 1, 'Great product, very satisfied with the purchase!', 5),
(302, 2, 'The product is fine, but the delivery was slow.', 3),
(304, 1, 'Good quality but didn’t meet my expectations.', 4),
(306, 1, 'Amazing! Will buy again for sure.', 5),
(308, 1, 'The product was not as described, very disappointed.', 2);


SELECT * FROM OrderDetails;


