const express = require("express");
const http = require("http");
const socketio = require("socket.io");
const app = express();
const server = http.createServer(app);
const io = socketio(server);
const db = require('./config/db.config.js');
const sendCustomerNotification = require('./config/helper.js');

const fs = require("fs");
const multer = require("multer");
const { exec } = require("child_process");
const { getVideoDurationInSeconds } = require("get-video-duration");

const crypto = require('crypto');


var base_url = "https://aadya.infosparkles.com/";

const UPLOADROOT = "/projects/aadya.infosparkles.com/public/uploads/";


const port = 5003;
app.get("/", function (req, res, next) {
    res.sendFile(__dirname + "/index.html");
});


server.listen(port, () => {
    console.log(`Server is up on port ${port}!`,);
});

/*(async () => {
  try {
    const token = await sendCustomerNotification('test title','test body','c9BfNwhOR0K4YPhAHVuVZs:APA91bHOGc7wQ-LCs2eAuby7to_O85ZJZvd-wK9fyB4y5q_WwgD7UcWE2WrMHYMkrFIZBI88Rf5UxoZuRJNMdXht6TS4Xu3_uroO_jrrRE-eBFvZrNTwrYY',{test:"test",test2:"test2"});
    //console.log(`Access Token: ${token}`);
    console.log("Result 1:", JSON.stringify(token, null, 2));
  } catch (err) {
    console.error("Error getting access token:", err.message);
  }
})();*/

var onlineusers = {};
var onlineusers_is_chatscreen = {};
io.on("connection", function (client) {
    //console.log("Client connected..." + client.id);
    client.on("join", function (data) {

        for (const [customer_id, socketId] of Object.entries(onlineusers)) {
            //console.log("Checking:", customer_id, socketId);
            if (socketId==client.id) {
                 delete onlineusers[customer_id];
                 delete onlineusers_is_chatscreen[customer_id];
            }
        }

        //console.log("is_online ", data);
        console.log("is_online "+data+" "+client.id);
        onlineusers[data] = client.id;
        io.emit("is_online", {customer_id: data,is_online: true,is_busy: false});
        io.emit("customerOrderIsOnline", {customer_id: data,is_online: true,is_busy: false});
        onlineOfflineDb(data, 1, (res) => {
            //console.log("User online:", res);
        });

        messagesDeliveredDB(data, (res) => {
            //console.log("User online:", res);
        });

        io.emit("is_delivered", {customer_id: data,is_delivered: true});

        /*send_Custome_Notification(282, (res) => {
            //console.log("User online:", res);
        });*/
        //console.log("User join onlineusers :", onlineusers);

        send_Custome_advisor_online(data);

    });

    client.on("messages", function (data) {
        console.log('messagesdata',data);
        savedb(data, function(result){
            //console.log("messages result",result)
            //console.log("onlineusers",onlineusers)
            // resultmsg = JSON.stringify(result)
            console.log('client.id',client.id);
            console.log('onlineusers[result.to]',onlineusers[result.to]);
            console.log('result.to',result.to);
            io.to(client.id).emit("broad", result);
            io.to(onlineusers[result.to]).emit("broad", result);

            // const customer_id = Object.keys(onlineusers).find(
            //     key => onlineusers[key] === result.to
            // );

            const customer_id = result.to; 
            const clientId = onlineusers[customer_id]; 

            /*console.log("onlineusers ",onlineusers)
            console.log("onlineusers to",result.to)
            console.log("onlineusers client.id ",client.id)
            console.log("onlineusers customer_id",customer_id)
            console.log("onlineusers clientId",clientId)*/
            if (clientId) {
                io.to(client.id).emit("is_delivered", {customer_id: result.to,is_delivered: true});
                messagesDeliveredDB(customer_id, (res) => {
                    //console.log("User online:", res);
                });
            }

            //console.log("onlineusers_is_chatscreen ",onlineusers_is_chatscreen)
            const customer_id1 = result.to; 
            const clientId1 = onlineusers_is_chatscreen[customer_id1]; 
            if (clientId1) {
            }else{
                send_Custome_Notification(result.chatID, function(res) {
                    //console.log("send_Custome_Notification res :", res);
                    if (clientId) {
                        io.to(clientId).emit("is_unread_message", {count: res.count,order_id: res.order_id});
                    }
                });
            }
        });
    });

    client.on("is_chatscreen", function (data) {
        //console.log("is_chatscreen:", data.is_chatscreen, "customer_id:", data.customer_id);
        if (data && data.customer_id) {
            if (data.is_chatscreen) {
                onlineusers_is_chatscreen[data.customer_id] = client.id;
            } else {
                delete onlineusers_is_chatscreen[data.customer_id];
            }
        }
    });


    client.on("typing", function (data) {
        // data = { from, to, isTyping }
        //console.log('typing........',data.to);
        if (onlineusers[data.to]) {
            io.to(onlineusers[data.to]).emit("typing", data);
        }
    });

    client.on("is_message_seen", function (data) {
        //console.log('is_message_seen ',data);
        messagesSeenDB(data, function(result){
            // const customer_id = Object.keys(onlineusers).find(
            //     key => onlineusers[key] === result.id
            // );
            //if (customer_id) {
                io.to(onlineusers[result.to]).emit("is_message_seen", {is_seen: true});
            //}
            //console.log('messagesSeenDB result',result);
            io.to(client.id).emit("is_unread_message", {count: 0,order_id: result.order_id});
        });
    });

    client.on("is_busy", function (data) {
        userIsbusyDb(data, (res) => {
            //console.log("User is_busy:", res);
            //console.log("User is_busy:", data.customer_id);
            io.emit("is_online", res);
            io.emit("customerOrderIsOnline", res);
        });
    });

    client.on("disconnect", function (data) {
        const customer_id = Object.keys(onlineusers).find(
            key => onlineusers[key] === client.id
        );

        //const customer_id = client.id; 
        //const clientId = onlineusers[customer_id]; 

        //console.log("User disconnect data :", data);
        //console.log("User disconnect customer_id :", customer_id);
        //console.log("User disconnect clientId :", clientId);
        if (customer_id) {
            io.emit("is_online", {customer_id: customer_id,is_online: false,is_busy: false});
            io.emit("customerOrderIsOnline", {customer_id: customer_id,is_online: false,is_busy: false});
            onlineOfflineDb(customer_id, 0, (res) => {
                //console.log("User offline:", res);
            });
            delete onlineusers_is_chatscreen[customer_id];
            delete onlineusers[customer_id];
        }
        //console.log("User disconnect onlineusers :", onlineusers);
        
        //delete onlineusers[data];
    });    
});

function onlineOfflineDb(customer_id, is_online, callback) {
    if (onlineusers[customer_id]) {
        //console.log('onlineOfflineDb',is_online);

        let sql, data;
        if (is_online==1) {
            let date = new Date();
            let is_online_last = date.toISOString().slice(0, 19).replace('T', ' ');
            
            sql = "UPDATE aa_customers SET is_online = ?, is_busy = ?, is_online_last = ? WHERE id = ?";
            data = [is_online, 0,is_online_last, customer_id];
            //console.log('onlineOfflineDb is_online_last ',is_online_last);
        }else{
            sql = "UPDATE aa_customers SET is_online = ?, is_busy = ? WHERE id = ?";
            data = [is_online, 0, customer_id];
        }

        //console.error("onlineOfflineDb customer_id :", customer_id);
        db.query(sql, data, function (err, result) {
            if (err) {
                //console.error("DB error:", err);
                //return callback(null); // or handle error
            }
            const datamsgnew = {
                customer_id: customer_id,
                is_online: is_online,
            };
            return callback(datamsgnew);
        });
    }
}

function userIsbusyDb(getdata, callback) {
    const isBusyValue = getdata.is_busy ? 1 : 0;

    const sql = "UPDATE aa_customers SET is_busy = ? WHERE id = ?";
    const data = [isBusyValue, getdata.customer_id];
    
    db.query(sql, data, function (err, result) {
        return callback(getdata);
    });
}

function messagesDeliveredDB(customer_id, callback) {
    //if (onlineusers[customer_id]) {
        const sql = "UPDATE aa_chats_2 SET is_delivered = ? WHERE to = ?";
        const data = [1, customer_id];
        db.query(sql, data, function (err, result) {
            if (err) {
                //console.error("DB error:", err);
                //return callback(null); // or handle error
            }
            const datamsgnew = {
                customer_id: customer_id,
            };
            return callback(datamsgnew);
        });
    //}
}

/*function messagesSeenDB(getdata, callback) {
    const sql = "UPDATE aa_chats_2 SET is_seen = ? WHERE chatID = ?";
    const data = [1, getdata.chatID];
    db.query(sql, data, function (err, result) {
        if (err) {
            //console.error("DB error:", err);
            //return callback(null); // or handle error
        }

        //console.error("messagesSeenDB :", result);
        const datamsgnew = {
            chatID: getdata.chatID,
            to: getdata.to,
        };
        return callback(datamsgnew);
    });
}*/

function messagesSeenDB(getdata, callback) {
    const sql = "UPDATE aa_chats_2 SET is_seen = ? WHERE chatID = ?";
    const data = [1, getdata.chatID];
    db.query(sql, data, function (err, result) {
        if (err) {
            //console.error("DB error:", err);
            //return callback(null); // or handle error
        }

        db.query("SELECT * FROM aa_chats_2 WHERE chatID = ?", [getdata.chatID], function (err2, chat_result) {
            if (err2) {
                //console.error("DB error:", err2);
                //return callback(null);
            }

            if (!chat_result[0]) return callback(null);

            const datamsgnew = {
                chatID: getdata.chatID,
                to: getdata.to,
                order_id: chat_result[0].order_id,
                //getchat: chat_result[0],
            };

            return callback(datamsgnew);
        });
    });
}

const util = require('util');
const query = util.promisify(db.query).bind(db);

async function send_Custome_Notification(chat_id,callback) {
    try {
        // 1️⃣ Get chat
        const chat_result = await query("SELECT * FROM aa_chats_2 WHERE chatID = ? AND is_seen = ?", [chat_id,0]);
        if (!chat_result[0]) return null;
        const getchat = chat_result[0];

        let datamsgnew = {};
        if (getchat) {
            // 2️⃣ Get order
            const order_result = await query("SELECT * FROM aa_orders WHERE id = ?", [getchat.order_id]);
            if (!order_result[0]) return null;
            const getorder = order_result[0];

            // 3️⃣ Get customer
            const customer_result = await query("SELECT * FROM aa_customers WHERE id = ?", [getchat.to]);
            if (!customer_result[0]) return null;
            const getcustomer = customer_result[0];


            const customer_result_userid = await query("SELECT * FROM aa_customers WHERE id = ?", [getchat.userid]);
            if (!customer_result_userid[0]) return null;
            const getcustomer_userid = customer_result_userid[0];

            

            // 4️⃣ Prepare notification
            if (getcustomer.fcm_token && getcustomer_userid) {
                const send_data = {
                    //title: 'You have received a new message.',
                    //body: 'You have received a new message.',
                    title: getcustomer_userid.full_name,
                    body: getchat.message,
                    screenType: 'chat',
                    orderId: String(getchat.order_id),
                    type: 'notification',
                };
                //console.log('total_charges ',getorder.total_charges);
                if (getcustomer.type === 'Customer') {
                    send_data.userType = 'Customer';
                    send_data.adviserId = String(getcustomer_userid.id);
                    send_data.adviserImage = getcustomer_userid.image
                        ? `${base_url}uploads/image/${getcustomer_userid.image}`
                        : `${base_url}uploads/placeholder/dummy_image.png`;
                    send_data.adviserName = getcustomer_userid.full_name;
                    send_data.totalPrice = String(getorder.total_charges);
                } else {
                    send_data.userType = 'Advisor';
                    send_data.customerId = String(getcustomer_userid.id);
                    send_data.availabilityId = '5';
                }

                const fcm_token = getcustomer.fcm_token;

                try {
                    const token = await sendCustomerNotification(
                        send_data.title,
                        send_data.body,
                        fcm_token,
                        send_data
                    );
                    // console.log("Notification Result:", token);
                } catch (err) {
                    //console.error("Error sending notification:", err.response?.data || err.message);
                }
            }

            const chat_count = await query("SELECT COUNT(*) AS unseen_count FROM aa_chats_2 WHERE `to` = ? AND `order_id` = ? AND is_seen = ?", [getchat.to,getchat.order_id, 0]);

            const unseenCount = chat_count[0]?.unseen_count || 0;
            datamsgnew = { chat_id: chat_id,count : unseenCount,order_id : getchat.order_id };
            return callback(datamsgnew);
        }

        //datamsgnew = { chat_id: chat_id,count : 0 };
        //return callback(datamsgnew);
    } catch (err) {
        //console.error("DB Error:", err);
        //return null;
    }
}


async function send_Custome_advisor_online(advisor_id) {
    try {
        //console.log('send_Custome_advisor_online ',advisor_id);
        const customer_result = await query("SELECT * FROM aa_customers WHERE id = ? AND type = ? AND status = ?", [advisor_id,'Advisor','Active']);
        if (!customer_result[0]) return null;
        const getAdvisor = customer_result[0];

        if (getAdvisor) {
            let wishlist_result = await query("SELECT * FROM aa_wishlist WHERE advisore_id = ?", [getAdvisor.id]);

            if (wishlist_result && wishlist_result.length > 0) {
                for (let wishlist of wishlist_result) {
                    //console.log('wishlist ',wishlist);
                    //console.log('wishlist. init ',wishlist.customer_id);
                    let customer_result_userid = await query("SELECT * FROM aa_customers WHERE id = ? AND type = ? AND status = ?", [wishlist.customer_id,'Customer','Active']);
                    if (customer_result_userid && customer_result_userid[0]){
                        let getcustomer = customer_result_userid[0];
                        if (getcustomer.fcm_token){
                            let fcm_token = getcustomer.fcm_token;
                            let send_data = {
                                title: 'Your favourite advisor is online.',
                                body: 'Your favourite advisor is online.',
                                screenType: 'advisor',
                                advisor_id: String(advisor_id),
                                type: 'notification',
                            };

                            //console.log('Your favourite advisor is online. ',getcustomer.id);
                            //console.log('Your favourite advisor is online fcm_token. ',fcm_token);

                            try {
                                let token = await sendCustomerNotification(
                                    send_data.title,
                                    send_data.body,
                                    fcm_token,
                                    send_data
                                );
                                // console.log("Notification Result:", token);
                            } catch (err) {
                                //console.error("Error sending notification:", err.response?.data || err.message);
                            }
                        }
                    }
                }
            }
        }
    } catch (err) {
        //console.error("DB Error:", err);
        //return null;
    }
}

async function savedb(datamsg,callback){    
    
    if (datamsg.userid > datamsg.to){
       var Groupid = datamsg.to+"_"+datamsg.userid;
    }else{
        var Groupid = datamsg.userid+"_"+datamsg.to;
    }   

    ts = Date.now();
    if(datamsg.type=='3'){
        var filename = ts+'.mov';
        var imagename = ts+'.png';
        var path = UPLOADROOT+'chat/'+filename
        var imgdata = datamsg.message;
        var base64Data = imgdata.replace(/^data:([A-Za-z-+/]+);base64,/, '');
        fs.writeFileSync(path, base64Data,  {encoding: 'base64'});

        videothumbnail(filename,imagename);

        var message = filename;
        var message_path = base_url+'uploads/chat/'+filename;
        var thumbnail = base_url+'uploads/chat/thumbnail/'+imagename;
        var getduration = '';

        var data = {
            userid: datamsg.userid,
            to: datamsg.to,
            message: message,
            Groupid: Groupid,
            type: datamsg.type,
            order_id: datamsg?.order_id,
            duration: getduration,
            CreatedDateTime: Math.floor(ts/1000),
        }; 
        
        var query = db.query('INSERT INTO aa_chats_2 SET ?', data, function(err,
            result) {
            //console.log(result);        
            var insertId = result.insertId;
            datamsgnew = {
                chatID: insertId,
                userid: datamsg.userid,
                to: datamsg.to,
                message: message_path,
                Groupid: Groupid,
                type: datamsg.type,
                order_id: datamsg?.order_id,
                thumbnail: thumbnail,
                duration: getduration,
                CreatedDateTime: Math.floor(ts/1000),
            }; 
            //console.log(datamsgnew);             
            return callback(datamsgnew);
        });
    }else if(datamsg.type=='1'){
        var filename = ts+'.png';
        var path = UPLOADROOT+'chat/'+filename
        var imgdata = datamsg.message;
        var base64Data = imgdata.replace(/^data:([A-Za-z-+/]+);base64,/, '');
        fs.writeFileSync(path, base64Data,  {encoding: 'base64'});

        var message = filename;
        var message_path = base_url+'uploads/chat/'+filename;
        var getduration = '';
        var thumbnail = '';

        var data = {
            userid: datamsg.userid,
            to: datamsg.to,
            message: message,
            Groupid: Groupid,
            type: datamsg.type,
            order_id: datamsg?.order_id,
            duration: getduration,
            CreatedDateTime: Math.floor(ts/1000),
        }; 
        
        var query = db.query('INSERT INTO aa_chats_2 SET ?', data, function(err,
            result) {
            //console.log(result);        
            var insertId = result.insertId;
            datamsgnew = {
                chatID: insertId,
                userid: datamsg.userid,
                to: datamsg.to,
                message: message_path,
                Groupid: Groupid,
                type: datamsg.type,
                order_id: datamsg?.order_id,
                thumbnail: thumbnail,
                duration: getduration,
                CreatedDateTime: Math.floor(ts/1000),
            }; 
            //console.log(datamsgnew);             
            return callback(datamsgnew);
        });
    }else if(datamsg.type=='2'){
        var getduration = '';
        var thumbnail = '';
        var filename = ts+'.m4a';
        var path = UPLOADROOT+'chat/'+filename
        var imgdata = datamsg.message;
        var base64Data = imgdata.replace(/^data:([A-Za-z-+/]+);base64,/, '');
        fs.writeFileSync(path, base64Data,  {encoding: 'base64'});

        var message = filename;
        var message_path = base_url+'uploads/chat/'+filename;
        getVideoDurationInSeconds(message_path).then((duration) => {
            
            d = Number(duration);
            var h = Math.floor(d / 3600);
            var m = Math.floor(d % 3600 / 60);
            var s = Math.floor(d % 3600 % 60);

            var hDisplay = h > 0 ? (h > 9 ? h : "0"+h) + " : " : "";
            var mDisplay = m > 0 ? (m > 9 ? m : "0"+m) + " : " : "00 : ";
            var sDisplay = s > 0 ? (s > 9 ? s : "0"+s) : "00";

            var getduration = hDisplay + mDisplay + sDisplay; 
            var data = {
                userid: datamsg.userid,
                to: datamsg.to,
                message: message,
                Groupid: Groupid,
                type: datamsg.type,
                order_id: datamsg?.order_id,
                duration: getduration,
                CreatedDateTime: Math.floor(ts/1000),
            }; 
            
            var query = db.query('INSERT INTO aa_chats_2 SET ?', data, function(err,
                result) {
                //console.log(result);        
                var insertId = result.insertId;
                datamsgnew = {
                    chatID: insertId,
                    userid: datamsg.userid,
                    to: datamsg.to,
                    message: message_path,
                    Groupid: Groupid,
                    type: datamsg.type,
                    order_id: datamsg?.order_id,
                    thumbnail: thumbnail,
                    duration: getduration,
                    CreatedDateTime: Math.floor(ts/1000),
                }; 
                //console.log(datamsgnew);                 
                return callback(datamsgnew);                
            });
        });
    }else{
        //var message      = datamsg.message;
        var message_path = datamsg.message;

        var message = await text_encrypt(datamsg.message);

        //console.log("savedb message",message)

        //var message_path = text_encrypt(datamsg.message);

        var getduration  = '';
        var thumbnail    = '';
        var data         = {
            userid         : datamsg.userid,
            to             : datamsg.to,
            message        : message.encrypted,
            key_id        : message.key_id,
            Groupid        : Groupid,
            type           : datamsg.type,
            order_id: datamsg?.order_id,
            duration       : getduration,
            CreatedDateTime: Math.floor(ts/1000),
        }; 
        
        var query = db.query('INSERT INTO aa_chats_2 SET ?', data, function(err,
            result) {       
            var insertId = result.insertId;
            datamsgnew = {
                chatID: insertId,
                userid: datamsg.userid,
                to: datamsg.to,
                message: message_path,
                Groupid: Groupid,
                type: datamsg.type,
                order_id: datamsg?.order_id,
                thumbnail: thumbnail,
                duration: getduration,
                CreatedDateTime: Math.floor(ts/1000),
            }; 
            //console.log(datamsgnew);
             
            return callback(datamsgnew);
        });
    }         
    
}

function secondsToHms(d) {
    d = Number(d);
    var h = Math.floor(d / 3600);
    var m = Math.floor(d % 3600 / 60);
    var s = Math.floor(d % 3600 % 60);

    var hDisplay = h > 0 ? (h > 9 ? h : "0"+h) + " : " : "";
    var mDisplay = m > 0 ? (m > 9 ? m : "0"+m) + " : " : "00 : ";
    var sDisplay = s > 0 ? (s > 9 ? s : "0"+s) : "00";

    return hDisplay + mDisplay + sDisplay; 
}
/*===================================================*/

function videothumbnail(videoname,imagename){
        var video =  UPLOADROOT+'chat/'+videoname;
    var image =  UPLOADROOT+'chat/thumbnail/'+imagename;
    var ffmpeg = '/usr/local/bin/ffmpeg';
    var second = 1;
    var thumbSize = '250x250';

    var cmdreturn = ffmpeg+' -i '+video+' -deinterlace -an -ss '+second+' -t 00:00:01 -s '+thumbSize+' -r 1 -y -vcodec mjpeg -f mjpeg '+image+' 2>&1'

    exec(cmdreturn, (error, stdout, stderr) => {
        if (error) {
            //console.log('error: {error.message}');
            return;
        }
        if (stderr) {
            //console.log('stderr: ${stderr}');
            return;
        }
        //console.log('stdout: ${stdout}');
    });
}

/*get Time String ===================================================*/
function get_time_string(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? "Pm" : "Am";
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? "0" + minutes : minutes;
    var strTime = hours + ":" + minutes + " " + ampm;
    return strTime;
}

async function getLatestKey() {
    const chat_keys_result = await query("SELECT * FROM aa_chat_keys ORDER BY id DESC LIMIT 1");
    if (!chat_keys_result[0]) return null;
    const chat_keys = chat_keys_result[0];
    if (chat_keys) {
        return chat_keys;
    } else {
        return null;
    }
}


async function text_encrypt(text) {
  const keyRow = await getLatestKey();
  if (keyRow) {
      const secretKey = keyRow.secret_key;
      const iv = crypto.randomBytes(16);

      const cipher = crypto.createCipheriv('aes-256-gcm', Buffer.from(secretKey, 'hex'), iv);
      let encrypted = cipher.update(text, 'utf8', 'hex');
      encrypted += cipher.final('hex');
      const authTag = cipher.getAuthTag().toString('hex');

      return {
        encrypted: `${iv.toString('hex')}:${authTag}:${encrypted}`,
        key_id: keyRow.id
      };
  }else{
     return {
        encrypted: text,
        key_id: null
      };
  }  
}