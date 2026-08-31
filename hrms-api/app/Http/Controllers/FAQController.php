<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionType;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FAQController extends Controller
{
    public function getQuestionTypes()
    {
        return response()->json([
            'success' => true,
            'message' => 'All Question Types successfully retrieved',
            'data' => QuestionType::orderBy('updated_at', 'desc')->get()
        ]);
    }

    public function getPaginatedQuestionTypes()
    {
        $questions = QuestionType::when(request()->q, function ($query) {
            $query->where('name', 'LIKE', '%' . request()->q . '%');
        })
            ->orderBy(request()->sort ?? 'name', request()->order ?? 'asc')
            ->paginate(request()->per_page ?? 10);
        return response()->json($questions);
    }

    public function getQuestions()
    {
        $questions = Question::with('questionType')
            ->when(request()->question_type_id, function ($query) {
                $query->where('question_type_id', request()->question_type_id);
            })
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'All Questions successfully retrieved',
            'data' => $questions
        ]);
    }

    public function getPaginatedQuestions()
    {
        $questions = Question::with('questionType')
            ->when(request()->q, function ($query) {
                $query->where('question', 'LIKE', '%' . request()->q . '%');
            })
            ->paginate(request()->per_page ?? 10);
        return response()->json($questions);
    }

    public function getAnswers()
    {
        $answers = Answer::when(request()->question_id, function ($query) {
            $query->where('question_id', request()->question_id);
            $query->orderBy('sequence');
        })
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'All Answers successfully retrieved',
            'data' => $answers
        ]);
    }

    public function createQuestionType(Request $request)
    {
        try {
            $validated = $this->validate($request, ['name' => 'required|string']);
            return response()->json([
                'success' => true,
                'message' => 'Question Type successfully created',
                'data' => QuestionType::create($validated),
            ]);
        } catch (ValidationException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => $error->errors()
            ], 422);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function createQuestion(Request $request)
    {
        try {
            $validated = $this->validate($request, [
                'question' => 'required|string',
                'question_type_id' => 'required|string'
            ]);
            QuestionType::findOrFail($validated['question_type_id']);
            return response()->json([
                'success' => true,
                'message' => 'Question successfully created',
                'data' => Question::create($validated),
            ]);
        } catch (ValidationException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => $error->errors()
            ], 422);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function createAnswer(Request $request)
    {
        try {
            $validated = $this->validate($request, [
                'answer' => 'required|string',
                'sequence' => 'required|int',
                'question_id' => 'required|string'
            ]);
            Question::findOrFail($validated['question_id']);
            return response()->json([
                'success' => true,
                'message' => 'Answer successfully created',
                'data' => Answer::create($validated),
            ]);
        } catch (ValidationException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => $error->errors()
            ], 422);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function updateQuestionType(Request $request, $questionTypeId)
    {
        try {
            $questionType = QuestionType::findOrFail($questionTypeId);
            $validated = $this->validate($request, ['name' => 'required|string']);
            return response()->json([
                'success' => $questionType->update($validated),
                'message' => 'Question Type successfully updated',
                'data' => null
            ]);
        } catch (ModelNotFoundException $error) {
            return response()->json([
                'success' => false,
                'message' => 'Question Type was not found',
                'data' => null
            ], 404);
        } catch (ValidationException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => $error->errors()
            ], 422);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function updateQuestion(Request $request, $questionId)
    {
        try {
            $question = Question::findOrFail($questionId);
            $validated = $this->validate($request, [
                'question' => 'required|string',
                'question_type_id' => 'required|string'
            ]);
            QuestionType::findOrFail($validated['question_type_id']);
            return response()->json([
                'success' => $question->update($validated),
                'message' => 'Question successfully updated',
                'data' => null
            ]);
        } catch (ModelNotFoundException $error) {
            return response()->json([
                'success' => false,
                'message' => 'Question or question type was not found',
                'data' => null
            ], 404);
        } catch (ValidationException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => $error->errors()
            ], 422);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function updateAnswer(Request $request, $answerId)
    {
        try {
            $answer = Answer::findOrFail($answerId);
            $validated = $this->validate($request, [
                'answer' => 'required|string',
                'sequence' => 'required|int',
                'question_id' => 'required|string'
            ]);
            Question::findOrFail($validated['question_id']);
            return response()->json([
                'success' => $answer->update($validated),
                'message' => 'Answer successfully updated',
                'data' => null
            ]);
        } catch (ModelNotFoundException $error) {
            return response()->json([
                'success' => false,
                'message' => 'Answer or question was not found',
                'data' => null
            ], 404);
        } catch (ValidationException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => $error->errors()
            ], 422);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function deleteQuestionType($questionTypeId)
    {
        try {
            $questionType = QuestionType::findOrFail($questionTypeId);
            if ($questionType->questions->count() > 0) {
                throw new Exception('This question type used by question data');
            }
            $questionType->delete();
        } catch (ModelNotFoundException $error) {
            return response()->json([
                'success' => false,
                'message' => 'Question type was not found',
                'data' => null
            ], 404);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function deleteQuestion($questionId)
    {
        try {
            $question = Question::findOrFail($questionId);
            if ($question->answers->count() > 0) {
                throw new Exception('This question used by answer data');
            }
            $question->delete();
        } catch (ModelNotFoundException $error) {
            return response()->json([
                'success' => false,
                'message' => 'Question was not found',
                'data' => null
            ], 404);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function deleteAnswer($answerId)
    {
        try {
            Answer::findOrFail($answerId)->delete();
        } catch (ModelNotFoundException $error) {
            return response()->json([
                'success' => false,
                'message' => 'Answer was not found',
                'data' => null
            ], 404);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
