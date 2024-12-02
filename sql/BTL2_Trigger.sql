---- check quantity when insert detail order--------
create or alter trigger PreventOverQuantityInOrderDetail
on OrderDetails
for insert, update
as 
begin
	if exists (select 1 from inserted, ProductVariant where inserted.product_id = ProductVariant.product_id 
															and inserted.pv_stt = ProductVariant.id
															and ProductVariant.quantity - inserted.quantity < 0)
	begin
		rollback;
		raiserror('EXCEED QUANTITY!!!', 16, 1);
	end
end
------ check address--------
create or alter trigger CheckAddressShipping
on Shipping
for insert
as
begin
	if not exists (select 1 from inserted, Orders, ShippingAddress where inserted.order_id = Orders.order_id 
																	and Orders.buyer_id = ShippingAddress.user_id
																	and inserted.shipping_address = ShippingAddress.address)
	begin
		rollback;
		raiserror('Address not exist!!!', 16, 1);
	end
end

select * from  Shipping
select * from ShippingAddress
select * from Orders
select * from ProductVariant
select * from OrderDetails