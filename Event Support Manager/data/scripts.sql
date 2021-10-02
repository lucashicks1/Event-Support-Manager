create table userbase(
   sid varchar(50) not null,
   lname varchar(50) not null,
   fname varchar(50) not null,
   yr varchar(50) not null,
   tutg varchar(50) not null,
   house varchar(50) not null,
   email varchar(50) not null,
   primary key(sid)
);

create table events(
   eid integer unsigned auto_increment not null,
   starttime datetime not null,
   endtime datetime not null,
   etype varchar(20) not null,
   ename varchar(100) not null,
   place varchar(30) not null,
   primary key(eid)
);

create table users(
   uid varchar(20) not null,
   utype varchar(10) not null,
   password varchar(64) not null,
   fname varchar(50) not null,
   lname varchar(50) not null,
   yr varchar(50),
   tutg varchar(50),
   house varchar(50),
   email varchar(50) unique not null,
   primary key(uid)
);

create table userevents(
   uid varchar(20) not null,
   eid integer unsigned not null,
   time datetime,
   alerts bit not null,
   sent bit not null,
   primary key(uid, eid),
   foreign key (uid) references users(uid),
   foreign key (eid) references events(eid)
);


-- ADMIN

INSERT INTO `users` (`uid`, `utype`, `password`, `fname`, `lname`, `yr`, `tutg`, `house`, `email`) VALUES ('admin', 'a', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', 'Admin', 'Name', NULL, NULL, NULL, 'admin@admin.com.au');