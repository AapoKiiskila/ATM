const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const card = require('../models/card_model');
const jwt = require('jsonwebtoken');
const dotenv = require('dotenv');

router.post('/', 
  function(request, response) {
    // Are both pin and card id given
    if(request.body.pin && request.body.idcard){
      // Assign card id and pin to variables
      const idcard = request.body.idcard;
      const pin = request.body.pin;
        // Check if pin is correct
        card.getById(idcard, function(dbError, dbResult) {
          if(dbError){
            response.json(dbError.errno);
          }
          else{
            if (dbResult.length > 0) {
              //Tarkistetaan, onko kortti lukittu
              card.getLockedInformation(idcard, function(dbErr, dbRes) {
                if(dbRes.length > 0) {
                  console.log("card is locked");
                  response.send(false);
                } else {
                  bcrypt.compare(pin,dbResult[0].pin, function(err,compareResult) {
                    if(compareResult) {
                      console.log("success");
                      const token = generateAccessToken({ card: idcard });
                      response.send(token);
                    }
                    else {
                        console.log("wrong pin code");
                        response.send(false);
                    }			
                  }
                  );
                }
              });
            }
            else{
              console.log("card does not exists");
              response.send(false);
            }
          }
          }
        );
      }
    else{
      console.log("card id or pin missing");
      response.send(false);
    }
  }
);

router.get('/setLocked/:id?',
function(request, response) {
  card.setLocked(request.params.id, function(err, dbResult) {
    if (err) {
      response.json(err);
    } else {
      response.json(dbResult);
    }
  })
});

function generateAccessToken(card) {
  dotenv.config();
  return jwt.sign(card, process.env.MY_TOKEN, { expiresIn: '900s' });
}

module.exports=router;