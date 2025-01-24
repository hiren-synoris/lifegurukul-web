const express = require("express");
require("dotenv").config();
const http = require("http");
const { createPool } = require("mysql2/promise"); // Import the 'mysql2/promise' package
const { Server } = require("socket.io");

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: "*",
  },
  rejectUnauthorized: false, // WARN: please do not do this in production
});

const pool = createPool({
  // Use 'createPool' function from 'mysql2/promise'
  host: process.env.DB_HOST,
  user: process.env.DB_USERNAME,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_DATABASE,
});
console.log(process.env.DB_HOST);
app.get("/", (req, res) => {
  res.sendFile(__dirname + "/index.html");
});

io.on("connection", (socket) => {
  console.log("A user connected");

  socket.on("timerData", async (datas) => {
    var {
      timer,
      learner_id_timer,
      chapter_id_timer,
      course_id_timer,
      buy_sell_course_id,
    } = datas;
    let Timerconnection;
    try {
      Timerconnection = await pool.getConnection();
      // console.log("1253");

      const query_course_timer =
        "SELECT id FROM sell_course_timers WHERE learner_id = ? AND chapter_id = ? AND course_id = ? ORDER BY id DESC LIMIT 1";
      const course_timer_select = [
        learner_id_timer,
        chapter_id_timer,
        course_id_timer,
      ];
      const [course_result] = await Timerconnection.query(
        query_course_timer,
        course_timer_select
      );

      if (course_result.length === 0) {
        const course_timer =
          "INSERT INTO sell_course_timers (timer,learner_id,course_id,chapter_id,buy_sell_course_id) VALUES (?,?,?,?,?)";
        const course_value = [
          timer,
          learner_id_timer,
          course_id_timer,
          chapter_id_timer,
          buy_sell_course_id,
        ];
        await Timerconnection.query(course_timer, course_value);
        console.log("time is inserted");
      } else {
        const query_course_timer_update =
          "UPDATE sell_course_timers SET timer = ? WHERE learner_id = ? AND chapter_id = ? AND course_id = ?";
        const course_timer_update = [
          timer,
          learner_id_timer,
          chapter_id_timer,
          course_id_timer,
        ];
        await Timerconnection.query(
          query_course_timer_update,
          course_timer_update
        );
        console.log("time is updated");
      }
    } catch (error) {
      console.error("Error executing queries:", error);
    } finally {
      // Release the connection
      try {
        if (Timerconnection) {
          Timerconnection.release();
          console.log("connection released");
        }
      } catch (error) {
        console.log("connection release error", error);
      }
    }
  });

  socket.on("updateData", async (data) => {
    var {
        learnerId,
        chapterId,
        watchedTime,
        isCompleted,
        currentDateTime
        // coins,
        // chapter_id,
        // course_id
    } = data;


    if (learnerId !== "" && chapterId !== "" && watchedTime !== "") {
        let connection;

        try {
            connection = await pool.getConnection();

            // let currentDateTime = getCurrentDateTime();



            const query1 =
                "SELECT id FROM user_course_progress WHERE learner_id = ? AND chapter_id = ? ORDER BY id DESC LIMIT 1";
            const values1 = [learnerId, chapterId];
            const [results1] = await connection.query(query1, values1);

            if (results1 && results1.length > 0 && results1[0].id) {
                const progress_id = results1[0].id;

                // Update the data in the MySQL database
                let query2;
                let values2;
                // let all_chapter_query;
                // let all_chapter_values;
                // let get_completed_query;
                // let get_completed_values;
                // let get_course_query;
                // let get_course_values;

                console.log(currentDateTime);
                if (isCompleted !== "" && isCompleted !== undefined) {
                    query2 =
                        "UPDATE user_course_progress SET watched_time = ?, is_completed = ?, updated_at = ? WHERE id = ?";
                    values2 = [watchedTime, isCompleted, currentDateTime, progress_id];

                    /*if (coins != null) { // if video coin added in the database
                        const user_coin =
                            "SELECT id FROM user_coins WHERE learner_id = ? AND chapter_id = ? AND type = ?";
                        const user_coin_values = [learnerId, chapterId, 5];
                        console.log("coin query",  user_coin);
                        const [user_coin_results] = await connection.query(
                            user_coin,
                            user_coin_values,
                        );
                        console.log("coin_ctn", user_coin_results.length);
                        if (user_coin_results.length == 0){ // if record not exist
                            const userCourse =
                                "INSERT INTO user_coins (learner_id, coins, type,chapter_id, comment,created_at) VALUES (?, ?, ?, ?, ?, ?)";
                            const userCourseValue = [
                                learnerId,
                                coins,
                                5,
                                chapter_id,
                                "earn by video completely watch",
                                currentDateTime
                            ];
                            await connection.query(userCourse, userCourseValue);
                        }
                    }*/

                } else {
                    query2 =
                        "UPDATE user_course_progress SET watched_time = ?, updated_at = ? WHERE id = ?";
                    values2 = [watchedTime, currentDateTime, progress_id];


                }

                await connection.query(query2, values2);

                // Full course compalited
                // if (isCompleted !== "" && isCompleted !== undefined) {

                //     all_chapter_query =
                //         "SELECT id FROM chapters WHERE course_id = ? AND parent_id != ?";
                //     all_chapter_values = [course_id, "0"];

                //     const [get_all_id_chapter] = await connection.query(all_chapter_query, all_chapter_values);

                //     const idArray = get_all_id_chapter.map(row => row.id);

                //     get_completed_query =
                //         "SELECT COUNT(*) AS count FROM user_course_progress WHERE chapter_id IN (?) AND learner_id = ?";
                //     get_completed_values = [idArray, learnerId];

                //     const [get_existed_count] = await connection.query(get_completed_query, get_completed_values);

                //     const count = get_existed_count[0].count;

                //     if (count == idArray.length) {

                //         console.log("course_id", course_id);

                //         get_course_query =
                //             "SELECT course_finished FROM courses WHERE id = ?";
                //         get_course_values = [course_id];

                //         const [get_course] = await connection.query(get_course_query, get_course_values);

                //         console.log("get_course", get_course[0].course_finished);

                //         if (get_course[0].course_finished) {
                //             const userCourse =
                //                 "INSERT INTO user_coins (learner_id, coins, type,chapter_id, comment) VALUES (?, ?, ?, ?,?)";
                //             const userCourseValue = [
                //                 learnerId,
                //                 get_course[0].course_finished,
                //                 7,
                //                 chapter_id,
                //                 "Eron coin to finished certain courses",
                //             ];
                //             await connection.query(userCourse, userCourseValue);

                //             const mailData = {
                //                 from: process.env.MAIL_FROM_ADDRESS,  // sender address
                //                 to: "jaiminkanzariya@gmail.com",
                //                 subject: 'Eron Coins',
                //                 text: 'CWIC Portal mail',
                //                 html: ForgotPasswordMailTemplate(params.name, params.forgot_link)
                //             };


                //             transporter.sendMail(mailData, function (err, info) {
                //                 if (err) {
                //                     console.log(err)
                //                     return false;
                //                 }
                //                 else {
                //                     return true;
                //                 }
                //             });
                //         }
                //     }
                // }


                // console.log('Data updated successfully!') ;
                // console.log(`Data updated successfully!! ++ id ${progress_id}, is_completed ${isCompleted}, watched_time ${watchedTime}`);
                console.log(`Data updated successfully!!`);
            } else {
                // Insert new entry
                if (isCompleted == "") {
                    isCompleted = 0;
                }

                const query2 =
                    "INSERT INTO user_course_progress (learner_id, chapter_id, watched_time, is_completed) VALUES (?, ?, ?, ?)";
                const values2 = [
                    learnerId,
                    chapterId,
                    watchedTime,
                    isCompleted,
                ];

                await connection.query(query2, values2);
                console.log(`Data inserted successfully!!`);
                // console.log(`Data inserted successfully!! ++ learner_id ${learnerId}, chapter_id ${chapterId}, watched_time ${watchedTime}, is_completed ${isCompleted}`);
            }

            const query3 =
                "SELECT * FROM user_course_progress WHERE learner_id = ? AND chapter_id = ? ORDER BY id DESC LIMIT 1";
            const values3 = [learnerId, chapterId];
            const [results2] = await connection.query(query3, values3);

            if (results2 && results2.length > 0 && results2[0].id) {
                const progress_id2 = results2[0].id;
                const learner_id2 = results2[0].learner_id;
                const chapter_id2 = results2[0].chapter_id;
                const watched_time2 = results2[0].watched_time;
                const is_completed2 = results2[0].is_completed;
                socket.send(
                    `Final Data: id ${progress_id2}, learner_id ${learner_id2}, chapter_id ${chapter_id2}, watched_time ${watched_time2}, is_completed ${is_completed2}`
                );
            }
        } catch (error) {
            console.error("Error executing queries:", error);
        } finally {
            // Release the connection
            try {
                if (connection) {
                    connection.release();
                    // console.log("connection released");
                }
            } catch (error) {
                console.log("connection release error", error);
            }
        }
    } else {
        socket.emit("videoTrackerError");
    }
});

  // Handle disconnection
  socket.on("disconnect", () => {
    console.log("A user disconnected");
  });
});

app.get("/node-status", (req, res) => {
  // Logic to retrieve the status from Laravel or perform any necessary checks

  // Example: Returning a custom status code (200 OK) and a message
  res.status(200).json({
    status: 200,
    message: "Node.js server is running",
  });
});

server.listen(3001, (error) => {
  if (error) {
    console.log("Something went wrong", error);
  } else {
    console.log("Server is listening on port 3000");
  }
});
