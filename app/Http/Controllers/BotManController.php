<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Incoming\Answer;
use Illuminate\Support\Facades\Log;
use App\Models\Todo;
use Illuminate\Support\Facades\Auth;

class BotManController extends Controller
{
    public function handle()
    {
        $botman = app('botman');

        $botman->hears('{message}', function ($botman, $message) {
            if (strtolower($message) == 'delete todo') {
                $this->askTodoIdToDelete($botman);
            } elseif (strtolower($message) == 'hi' || strtolower($message) == 'hello') {
                $this->askName($botman);
            }
        });

        $botman->hears('add todo', function (BotMan $botman) {
            $this->askTodoTask($botman);
        });

        $botman->hears('show my todos', function (BotMan $botman) {
            $this->showPendingTodos($botman);
        });

        $botman->hears('delete todo {id}', function (BotMan $botman, $id) {
            $this->deleteTodo($botman, $id);
        });

        $botman->hears('update todo {id} {task}', function (BotMan $botman, $id, $task) {
            $this->updateTodoTask($botman, $id, $task);
        });

        $botman->hears('complete todo {id}', function (BotMan $botman, $id) {
            $this->completeTodoTask($botman, $id);
        });

        $botman->fallback(function (BotMan $botman) {
            $botman->reply('Sorry, I did not understand that. Can you please try again?');
        });

        $botman->listen();
    }

    public function askName($botman)
    {
        $botman->ask('Hello! What is your Name?', function (Answer $answer) {
            $name = $answer->getText();
            $this->say('Nice to meet you ' . $name);
            $this->say('How can I assist you, ' . $name . '?');
        });
    }

    public function askTodoTask(BotMan $botman)
    {
        $botman->ask('What is the task you want to add to your todo list?', function (Answer $answer) {
            $task = $answer->getText();

            if (empty($task)) {
                $this->say('Task cannot be empty. Please provide a valid task.');
                return;
            }

            try {
                $todo = Todo::create(['task' => $task, 'completed' => false, 'user_id' => Auth::id()]);
                $this->say('Your todo item has been added successfully!');
            } catch (\Exception $e) {
                Log::error('Error adding todo: ' . $e->getMessage());
                $this->say('There was an error adding your todo item. Please try again later.');
            }
        });
    }

    public function showPendingTodos(BotMan $botman)
    {
        $todos = Todo::where('user_id', Auth::id())->where('completed', false)->get();

        if ($todos->isEmpty()) {
            $botman->reply('You have no pending todos.');
        } else {
            $reply = 'Your pending todos are: <br>';
            foreach ($todos as $todo) {
                $reply .= "\n" . $todo->id . ' - ' . $todo->task . '<br>';
            }
            $botman->reply($reply);
            $botman->ask('Would you like to mark any of these tasks as completed? If yes, please reply with "complete todo {id}".', function (Answer $answer, BotMan $botman) {
                $response = strtolower($answer->getText());
                if (preg_match('/complete todo (\d+)/', $response, $matches)) {
                    $id = $matches[1];
                    $this->completeTodoTask($botman, $id);
                } else {
                    $botman->reply('Okay, let me know if you need anything else.');
                }
            });
        }
    }

    public function deleteTodo(BotMan $botman, $id)
    {
        try {
            $todo = Todo::find($id);

            if (is_null($todo) || $todo->user_id != Auth::id()) {
                $botman->reply('Invalid ID provided or you do not have permission to delete this todo.');
            } else {
                $todo->delete();
                $botman->reply('You have successfully deleted the todo: "' . $todo->task . '"');
            }
        } catch (\Exception $e) {
            Log::error('Error deleting todo: ' . $e->getMessage());
            $botman->reply('An error occurred while deleting the todo. Please try again later.');
        }
    }

    public function updateTodoTask(BotMan $botman, $id, $task)
    {
        try {
            $todo = Todo::find($id);

            if (is_null($todo) || $todo->user_id != Auth::id()) {
                $botman->reply('Invalid ID provided or you do not have permission to update this todo.');
            } else {
                $todo->task = $task;
                $todo->save();
                $botman->reply('Todo task with ID ' . $id . ' has been updated to: "' . $task . '"');
            }
        } catch (\Exception $e) {
            Log::error('Error updating todo: ' . $e->getMessage());
            $botman->reply('An error occurred while updating the todo task. Please try again later.');
        }
    }

    public function completeTodoTask(BotMan $botman, $id)
    {
        try {
            $todo = Todo::find($id);

            if (is_null($todo) || $todo->user_id != Auth::id()) {
                $botman->reply('Invalid ID provided or you do not have permission to mark this todo as completed.');
            } else {
                $todo->completed = true;
                $todo->save();
                $botman->reply('Todo task with ID ' . $id . ' has been marked as completed.');
            }
        } catch (\Exception $e) {
            Log::error('Error completing todo: ' . $e->getMessage());
            $botman->reply('An error occurred while marking the todo as completed. Please try again later.');
        }
    }
}
