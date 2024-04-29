
DELIMITER //
DROP procedure IF EXISTS nostotapahtuma;
CREATE PROCEDURE nostotapahtuma(IN asiakas INT,IN tilin_numero INT, IN nosto DECIMAL(10,2))
BEGIN
    DECLARE tarkistettava_balance DECIMAL(10,2);
    SELECT balance INTO tarkistettava_balance 
    FROM account 
    WHERE idaccount = tilin_numero;

 IF EXISTS (SELECT * FROM customer_has_account WHERE idcustomer = asiakas AND idaccount = tilin_numero) THEN
        IF tarkistettava_balance IS NOT NULL AND tarkistettava_balance >0 AND tarkistettava_balance-nosto>=0 THEN
        UPDATE account 
        SET balance = balance - nosto 
        WHERE idaccount = tilin_numero;
        INSERT INTO withdrawal(idaccount, amount, timestamp) VALUES (tilin_numero, nosto, NOW());

        ELSEIF  tarkistettava_balance-nosto < 0 THEN
            SELECT 'You do not have enough money for that sum';
        ELSE
            SELECT 'You have no balance, U broke :)';
        END IF;
ELSE
    SELECT 'You do not have accest to that account';
END IF;

END//
    
-- PhP:ssa käytettävät proseduurit
    
DROP procedure IF EXISTS new_card;
CREATE PROCEDURE new_card(IN use_customer INT,IN combine_account INT, IN combine_account2 INT, IN newcardcode VARCHAR(255),IN new_pin VARCHAR(225))
BEGIN
    DECLARE account_type1 VARCHAR(50);
    DECLARE account_type2 VARCHAR(50);
    SELECT credit INTO account_type1 FROM account WHERE idaccount = combine_account;
    SELECT credit INTO account_type2 FROM account WHERE idaccount = combine_account2;
IF combine_account2 IS NULL THEN
  IF EXISTS(SELECT 1 FROM account WHERE idaccount = combine_account) THEN
      IF NOT EXISTS(SELECT 1 FROM card WHERE idcard = newcardcode) THEN
          INSERT INTO card(idcard, pin, idcustomer) VALUES(newcardcode,new_pin, use_customer);
          INSERT INTO card_has_account(idcard,idaccount) VALUES (newcardcode, combine_account);
          INSERT INTO card_locked(idcard,locked) VALUES (newcardcode,false);
          SELECT 'SUCCHEESE';
       ELSE
          SELECT 'Card already exists';
          END IF;
  ELSE
      SELECT 'Account does not exist';
  END IF;
ELSE 
  IF EXISTS(SELECT 1 FROM account WHERE idaccount = combine_account) THEN
      IF EXISTS(SELECT 1 FROM account WHERE idaccount = combine_account2) THEN
          IF account_type1 IS NULL XOR account_type2 IS NULL THEN
                 IF NOT EXISTS(SELECT 1 FROM card WHERE idcard = newcardcode) THEN
                     INSERT INTO card(idcard, pin, idcustomer) VALUES(newcardcode,new_pin, use_customer);
                      INSERT INTO card_has_account(idcard,idaccount) VALUES (newcardcode, combine_account);
                      INSERT INTO card_has_account(idcard,idaccount) VALUES (newcardcode, combine_account2);
                      INSERT INTO card_locked(idcard,locked) VALUES (newcardcode,false);
                      SELECT 'SUCCHEESE';
               ELSE
                   SELECT 'Card already exists';
               END IF;
           ELSE
               SELECT 'Accounts are the same type';
           END IF;
       ELSE
           SELECT 'Credit accout does not exists';
       END IF;
   ELSE
       SELECT 'Debit account does not exists ' ;
   END IF;
END IF;
END//

    
DROP procedure IF EXISTS new_account;
CREATE PROCEDURE new_account(IN asiakas INT,IN new_balance DECIMAL(10,2), IN new_credit DECIMAL(10,2))
BEGIN
DECLARE new_account_id INT;
INSERT INTO account(balance, credit) VALUES(new_balance, new_credit);
SET new_account_id = LAST_INSERT_ID();
INSERT INTO customer_has_account(idcustomer,idaccount) VALUES (asiakas, new_account_id);
END//


DROP procedure IF EXISTS delete_card;
CREATE PROCEDURE delete_card(IN del_card VARCHAR(16))
BEGIN
DELETE FROM card_has_account WHERE idcard = del_card;
DELETE FROM card WHERE idcard = del_card;
END//

    
DROP procedure IF EXISTS delete_account;
CREATE PROCEDURE delete_account(IN del_account INT)
BEGIN
DECLARE card_exists VARCHAR(16);
DECLARE many_cards INT;
DECLARE i INT ;
SET i = 0;
SELECT COUNT(card.idcard) INTO many_cards FROM account JOIN card_has_account ON card_has_account.idaccount = account.idaccount JOIN card ON card_has_account.idcard = card.idcard WHERE account.idaccount = del_account;
IF many_cards != 0 THEN
    poisto : LOOP
        IF many_cards>=i THEN
            SELECT idcard INTO card_exists FROM card_has_account WHERE idaccount = del_account LIMIT 1;
                 DELETE FROM card_has_account WHERE idcard = card_exists;
                 DELETE FROM card WHERE idcard = card_exists;
            SET i = i+1;
        ELSE
            LEAVE poisto;
        END IF;
    END LOOP;
END IF;
DELETE FROM customer_has_account WHERE idaccount = del_account;
DELETE FROM withdrawal WHERE idaccount = del_account;
DELETE FROM account WHERE idaccount = del_account;
END//

DELIMITER ;
