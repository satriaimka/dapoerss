-- Add reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    order_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_order (user_id, product_id, order_id)
);

-- Add trigger to update product rating when review is added
DELIMITER //
CREATE TRIGGER update_product_rating_after_review
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET rating = (
        SELECT AVG(rating) 
        FROM reviews 
        WHERE product_id = NEW.product_id
    ),
    reviews_count = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE product_id = NEW.product_id
    )
    WHERE id = NEW.product_id;
END//
DELIMITER ;

-- Add trigger to update product rating when review is updated
DELIMITER //
CREATE TRIGGER update_product_rating_after_review_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET rating = (
        SELECT AVG(rating) 
        FROM reviews 
        WHERE product_id = NEW.product_id
    ),
    reviews_count = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE product_id = NEW.product_id
    )
    WHERE id = NEW.product_id;
END//
DELIMITER ;

-- Add trigger to update product rating when review is deleted
DELIMITER //
CREATE TRIGGER update_product_rating_after_review_delete
AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET rating = COALESCE((
        SELECT AVG(rating) 
        FROM reviews 
        WHERE product_id = OLD.product_id
    ), 0),
    reviews_count = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE product_id = OLD.product_id
    )
    WHERE id = OLD.product_id;
END//
DELIMITER ;
