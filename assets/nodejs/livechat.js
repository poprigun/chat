// Export a function, so that we can pass
// the app and io instances from the app.js file:

// room array
var rooms = {};
module.exports = function(app,io){

	// Initialize a new socket.io application, named 'chat'
	var chat = io.on('connection', function (socket) {
		// create|add user to chat
		socket.on('ready',function(data){
			createRooms(data.uid);
		});
		// join to chat (one chat)
		socket.on('join',function(data){

			var room = findClientsSocket(data.cid);

			// Use the socket object to store data. Each client gets
			// their own unique socket object

			socket.user = data.user;
			socket.room = data.cid;

			// Add the client to the room
			socket.join(data.cid);

			socket.broadcast.to(data.cid).emit('peopleInChat', {
				cid:data.cid,
				users: room
			});
		});

		socket.on('typing',function(data){
			var room = findClientsSocket(data.cid);
			socket.broadcast.to(data.cid).emit('typing', {
				cid: data.cid,
				user: data.user
			});
		});
		// Somebody left the chat
		socket.on('disconnect', function() {
			removeFromRoom(this.user);
			socket.broadcast.to(this.room).emit('leave', {
				room: this.room,
				user: this.user
			});
			// leave the room
			socket.leave(socket.room);
		});
		// Handle the sending of messages
		socket.on('send', function(data){
			// When the server receives a message, it sends it to the other person in the room.
			socket.broadcast.to(this.room).emit('receive',data);
		});
	});
};

// find|create room
function findClientsSocket(roomId){

	return rooms[roomId];
}
// add/create chat rooms
function createRooms(data){

	for(var user in data){

		for(var key in data[user]){

			room = data[user][key];
			if(rooms[room] == undefined){
				rooms[room] = [];
			}

			if(rooms[room].indexOf(user) == -1){
				rooms[room].push(user);
			}

		}
	}
}
// remove from chat rooms
function removeFromRoom(user){
	for(var room in rooms){
		index = rooms[room].indexOf(user)
		if(index){
			rooms[room].splice(index,1)
		}

	}
}
