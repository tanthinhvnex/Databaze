--Register user
CREATE PROCEDURE InsertUser
    @Firstname NVARCHAR(50),
    @Lastname NVARCHAR(50),
    @Email NVARCHAR(255),
    @Password NVARCHAR(255),
    @PhoneNumber NVARCHAR(10)
AS
BEGIN
    IF NOT (@Email LIKE '%_@_%._%')
    BEGIN
        RAISERROR('Invalid email format',16,1);
        RETURN;
    END;
    BEGIN TRY
        INSERT INTO Users(first_name,last_name,email,password,phone_number)
        VALUES (@Firstname,@Lastname,@Email,@Password,@PhoneNumber)
        PRINT 'User has been inserted successfully';
    END TRY
    BEGIN CATCH
        PRINT 'Error occurred while inserting user';
    END CATCH
END;
GO
--Update info user
CREATE PROCEDURE UpdateUser
    @UserID INT,                -- ID người dùng cần cập nhật
    @FirstName NVARCHAR(50),    -- Tên mới
    @LastName NVARCHAR(50),     -- Họ mới
    @Email NVARCHAR(255),       -- Email mới
    @PhoneNumber NVARCHAR(15)   -- Số điện thoại mới
AS
BEGIN
    -- Kiểm tra xem UserID có tồn tại hay không
    IF NOT EXISTS (SELECT 1 FROM Users WHERE user_id = @UserID)
    BEGIN
        RAISERROR('UserID không tồn tại.', 16, 1);
        RETURN;
    END

    -- Kiểm tra định dạng email
    IF NOT (@Email LIKE '%_@_%.__%')
    BEGIN
        RAISERROR('Email không hợp lệ.', 16, 1);
        RETURN;
    END

    -- Kiểm tra định dạng số điện thoại (phải là số và có độ dài hợp lệ)
    IF NOT (@PhoneNumber LIKE '[0-9]%')
    BEGIN
        RAISERROR('Số điện thoại không hợp lệ.', 16, 1);
        RETURN;
    END

    -- Thực hiện cập nhật thông tin người dùng
    UPDATE Users
    SET 
        first_name = @FirstName,
        last_name = @LastName,
        email = @Email,
        phone_number = @PhoneNumber
    WHERE user_id = @UserID;

    -- Xác nhận cập nhật thành công
    PRINT 'Thông tin người dùng đã được cập nhật thành công.';
END;
GO
--seller thêm sản phẩm
CREATE PROCEDURE InsertProduct
    @SellerID INT,
    @ProductName NVARCHAR(255),
    @Size NVARCHAR(50),
    @Color NVARCHAR(50),
    @Price DECIMAL(10, 2),
    @StockQuantity INT,
    @Description NVARCHAR(500),
    @Category NVARCHAR(50), -- 'UpperWear' hoặc 'LowerWear'
    @SleeveLength NVARCHAR(50) = NULL, -- Chỉ cho UpperWear
    @NeckStyle NVARCHAR(50) = NULL,   -- Chỉ cho UpperWear
    @WaistSize NVARCHAR(50) = NULL,   -- Chỉ cho LowerWear
    @LegLength NVARCHAR(50) = NULL    -- Chỉ cho LowerWear
AS
BEGIN
    BEGIN TRY
        -- Validate giá
        IF @Price <= 0
        BEGIN
            RAISERROR('Giá sản phẩm phải lớn hơn 0.', 16, 1);
        END;

        -- Validate số lượng tồn kho
        IF @StockQuantity < 0
        BEGIN
            RAISERROR('Số lượng không được âm', 16, 1);

        END;

        --category ở dạng chọn input
        
        --Kiểm tra sản phẩm tồn tại chưa
        DECLARE @ExistingProductID INT;
        SELECT @ExistingProductID = product_id
        FROM Products
        WHERE product_name = @ProductName
        IF @ExistingProductID IS NOT NULL
        BEGIN
            -- Sản phẩm đã tồn tại trong bảng Products, kiểm tra biến thể
            DECLARE @ExistingVariantID INT;
            SELECT @ExistingVariantID = id
            FROM ProductVariant
            WHERE product_id = @ExistingProductID
              AND color = @Color
              AND size = @Size;

            IF @ExistingVariantID IS NOT NULL
            BEGIN
                -- Biến thể đã tồn tại, cập nhật số lượng
                UPDATE ProductVariant
                SET quantity = quantity + @StockQuantity
                WHERE id = @ExistingVariantID AND product_id = @ExistingProductID;

                PRINT 'Cập nhật số lượng biến thể thành công.';
            END
            ELSE
            BEGIN
                -- Biến thể chưa tồn tại, khởi tạo id mới bắt đầu từ 0
                DECLARE @NewVariantID INT;
                SELECT @NewVariantID = ISNULL(MAX(id), 0) + 1
                FROM ProductVariant
                WHERE product_id = @ExistingProductID;

                INSERT INTO ProductVariant (id, product_id, color, size, quantity)
                VALUES (@NewVariantID, @ExistingProductID, @Color, @Size, @StockQuantity);

                PRINT 'Thêm biến thể sản phẩm mới thành công.';
            END
        END
        ELSE
        BEGIN
            DECLARE @ProductID INT;

            INSERT INTO Products (seller_id, product_name, price, description, category)
            VALUES (@SellerID, @ProductName, @Price, @Description, @Category);

            SET @ProductID = SCOPE_IDENTITY();

            -- Thêm biến thể mới vào ProductVariant
            INSERT INTO ProductVariant (id, product_id, color, size, quantity)
            VALUES (0, @ProductID, @Color, @Size, @StockQuantity);

            -- Thêm dữ liệu vào UpperWear hoặc LowerWear
            IF @Category = 'UpperWear'
            BEGIN
                IF @SleeveLength IS NULL OR @NeckStyle IS NULL
                    THROW 50003, 'SleeveLength và NeckStyle là bắt buộc với UpperWear.', 1;

                INSERT INTO UpperWear (upper_id, sleeve_length, neck_type)
                VALUES (@ProductID, @SleeveLength, @NeckStyle);
            END
            ELSE IF @Category = 'LowerWear'
            BEGIN
                IF @WaistSize IS NULL OR @LegLength IS NULL
                    THROW 50004, 'WaistSize và LegLength là bắt buộc với LowerWear.', 1;

                INSERT INTO LowerWear (lower_id, waist_style, leg_length)
                VALUES (@ProductID, @WaistSize, @LegLength);
            END;       
            PRINT 'Thêm sản phẩm thành công.';
        END;
    END TRY
    BEGIN CATCH
        PRINT ERROR_MESSAGE();
    END CATCH
END;
GO

--Xóa sản phẩm
CREATE PROCEDURE DeleteProduct
    @ProductID INT -- ID của sản phẩm cần xóa
AS
BEGIN
    SET NOCOUNT ON;

    BEGIN TRY
        -- Kiểm tra sản phẩm có tồn tại hay không
        IF NOT EXISTS (SELECT 1 FROM Products WHERE product_id = @ProductID)
        BEGIN
            RAISERROR('Sản phẩm không tồn tại.', 16, 1);
        END;
        --Xóa khỏi Products
        DELETE FROM Products
        WHERE product_id = @ProductID;

        PRINT 'Xóa sản phẩm thành công.';
    END TRY
    BEGIN CATCH
        -- Xử lý lỗi
        PRINT ERROR_MESSAGE();
    END CATCH
END;
GO
--Cập nhật sản phẩm
CREATE PROCEDURE UpdateProductBasic
    @ProductID INT,              
    @Description NVARCHAR(500) = NULL,
    @Price DECIMAL(10, 2) = NULL 
AS
BEGIN
    SET NOCOUNT ON;

    BEGIN TRY
        -- Kiểm tra sản phẩm có tồn tại hay không
        IF NOT EXISTS (SELECT 1 FROM Products WHERE product_id = @ProductID)
        BEGIN
            RAISERROR('Sản phẩm không tồn tại.', 16, 1);
            RETURN;
        END;

        -- Cập nhật giá và mô tả sản phẩm
        UPDATE Products
        SET 
            price = ISNULL(@Price, price),            
            description = ISNULL(@Description, description) 
        WHERE product_id = @ProductID;

        PRINT 'Cập nhật sản phẩm thành công.';
    END TRY
    BEGIN CATCH
        -- Xử lý lỗi và in thông báo lỗi
        PRINT ERROR_MESSAGE();
    END CATCH
END;
GO
--User review
CREATE PROCEDURE InsertReview
    @OrderID INT,               -- ID of the associated order
    @OrderDetails_ID INT,      -- ID of the product variant in the order detail
    @Rating INT,                -- Rating value
    @Comment NVARCHAR(250),     -- Comment text
    @ReviewDate DATE = NULL     -- Review date, defaults to NULL (use current date if not provided)
AS
BEGIN
    -- Validate input
    IF @Rating < 1 OR @Rating > 5
    BEGIN
        RAISERROR('Invalid rating value. Must be between 1 and 5.', 16, 1);
        RETURN;
    END;

    -- Validate OrderDetails exists with the given composite key
    IF NOT EXISTS (
        SELECT 1
        FROM OrderDetails
        WHERE order_id = @OrderID AND orderDetail_ID = @OrderDetails_ID
    )
    BEGIN
        RAISERROR('Order detail does not exist for the provided OrderID and ProductVariantID.', 16, 1);
        RETURN;
    END;

    -- Insert review
    BEGIN TRY
        INSERT INTO Reviews (order_id,orderDetail_ID,comment,rating,review_date)
        VALUES (
            @OrderID,
            @OrderDetails_ID,
            @Comment,
            @Rating,
            ISNULL(@ReviewDate, GETDATE())
        );

        PRINT 'Review has been successfully inserted.';
    END TRY
    BEGIN CATCH
        -- Handle errors
        PRINT ERROR_MESSAGE();
    END CATCH
END;
GO
--
CREATE PROCEDURE InsertPayment
    @OrderID INT,
    @PaymentMethod NVARCHAR(50), -- Phương thức thanh toán
    @RefCode NVARCHAR(255) = NULL -- Mã tham chiếu (có thể null)
AS
BEGIN
    BEGIN TRY
        -- Kiểm tra xem UserID có tồn tại
        IF NOT EXISTS (SELECT 1 FROM Orders WHERE order_id = @OrderID)
        BEGIN
            RAISERROR('Id không tồn tại.', 16, 1);
            RETURN;
        END

        -- Thêm thông tin thanh toán vào bảng Payment
        INSERT INTO Payment (order_id, payment_method, ref_code)
        VALUES (@OrderID, @PaymentMethod, @RefCode);
        PRINT 'Thêm thông tin thanh toán thành công.';
    END TRY
    BEGIN CATCH
        PRINT ERROR_MESSAGE();
    END CATCH
END;
GO
--
CREATE PROCEDURE InsertShipping
    @OrderID INT,              -- Order associated with the shipping
    @ShippingAddress NVARCHAR(255),
    @ShippingFee DECIMAL(10, 2),
    @ShippingPartner NVARCHAR(50),
    @TrackingNumber NVARCHAR(50),
    @Driver NVARCHAR(50)
AS
BEGIN
    BEGIN TRY
        INSERT INTO Shipping (order_id, tracking_number, shipping_address, shipping_partner, driver_name, shipping_fee)
        VALUES (@OrderID, @TrackingNumber, @ShippingAddress, @ShippingPartner, @Driver, @ShippingFee);

        PRINT 'Shipping record has been successfully added.';
    END TRY
    BEGIN CATCH
        PRINT ERROR_MESSAGE();
    END CATCH
END;
GO
