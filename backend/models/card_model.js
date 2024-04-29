const db = require('../mods/database');
const bcrypt = require('bcryptjs');

const saltRounds=10;
const card={
  getAll: function(callback) {
    return db.query('select * from card', callback);
  },
  getById: function(id, callback) {
    return db.query('select * from card where idcard=?', [id], callback);
  },
  getLockedInformation: function(id, callback) {
    return db.query('SELECT * FROM card_locked WHERE idcard=? AND locked = 1', [id], callback);
  },
  setLocked: function(id, callback) {
    return db.query('insert into card_locked (idcard, locked) values(?, 1)', [id], callback);
  },
  getAccountDetails: function(id, callback) {
    return db.query('SELECT account.* FROM account JOIN card_has_account ON card_has_account.idaccount = account.idaccount JOIN card ON card_has_account.idcard = card.idcard WHERE card.idcard =?;', [id], callback);
  },
  add: function(card, callback) {
    bcrypt.hash(card.pin, saltRounds, function(err, hash) {
      return db.query('insert into card (pin, idcustomer, idcard) values(?,?,?)',
      [hash, card.idcustomer, card.idcard], callback);
    });
  },
  delete: function(id, callback) {
    return db.query('delete from card where idcard=?', [id], callback);
  },
  update: function(id, card, callback) {
    bcrypt.hash(card.pin, saltRounds, function(err, hash) {
      return db.query('update card set pin=?, idcustomer=?, idcard=? where idcard=?',
      [hash, card.idcustomer, card.idcard, id], callback);
    });
  }

}
          
module.exports = card;