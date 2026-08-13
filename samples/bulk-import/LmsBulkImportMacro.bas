' LMS Bulk Import Helper for Microsoft Word
' ------------------------------------------
' How to install:
' 1. Open Microsoft Word
' 2. Press Alt+F11 to open the VBA editor
' 3. File > Import File… and choose this .bas module
'    OR Insert > Module and paste this code
' 4. Save the document as a .docm (macro-enabled) if you want the macro stored in the file
' 5. Alt+F8 → run InsertQuizQuestion / InsertAssignmentBlock / ValidateLmsImport
'
' Tip: Start from the LMS template downloaded from Bulk Import Templates,
' then use these macros so every block keeps the exact labels the LMS parser expects.

Option Explicit

Public Sub InsertQuizHeader()
    Selection.TypeText "LMS_IMPORT: QUIZ" & vbCrLf & vbCrLf
End Sub

Public Sub InsertQuestionBankHeader()
    Selection.TypeText "LMS_IMPORT: QUESTION_BANK" & vbCrLf & vbCrLf
End Sub

Public Sub InsertQuizQuestion()
    Dim n As Long
    n = CountQuizQuestions() + 1

    Selection.TypeText "Q" & n & ". [Write the question prompt here]" & vbCrLf
    Selection.TypeText "A) [Option A]" & vbCrLf
    Selection.TypeText "B) [Option B]" & vbCrLf
    Selection.TypeText "C) [Option C]" & vbCrLf
    Selection.TypeText "D) [Option D]" & vbCrLf
    Selection.TypeText "ANSWER: A" & vbCrLf & vbCrLf
End Sub

Public Sub InsertAssignmentsHeader()
    Selection.TypeText "LMS_IMPORT: ASSIGNMENTS" & vbCrLf & vbCrLf
End Sub

Public Sub InsertAssignmentBlock()
    Selection.TypeText "---" & vbCrLf
    Selection.TypeText "TITLE: [Assignment title]" & vbCrLf
    Selection.TypeText "INSTRUCTIONS: [What students should submit]" & vbCrLf
    Selection.TypeText "DELIVERY: file" & vbCrLf
    Selection.TypeText "DUE: 2026-09-15 23:59" & vbCrLf
    Selection.TypeText "PUBLISH: no" & vbCrLf
    Selection.TypeText "---" & vbCrLf & vbCrLf
End Sub

Public Sub ValidateLmsImport()
    Dim txt As String
    Dim msg As String
    txt = ActiveDocument.Content.Text

    If InStr(1, txt, "LMS_IMPORT:", vbTextCompare) = 0 Then
        MsgBox "Missing LMS_IMPORT: line at the top (QUIZ, QUESTION_BANK, or ASSIGNMENTS).", vbExclamation
        Exit Sub
    End If

    If InStr(1, txt, "LMS_IMPORT: QUIZ", vbTextCompare) > 0 _
        Or InStr(1, txt, "LMS_IMPORT: QUESTION_BANK", vbTextCompare) > 0 Then
        msg = ValidateQuiz(txt)
    ElseIf InStr(1, txt, "LMS_IMPORT: ASSIGNMENTS", vbTextCompare) > 0 Then
        msg = ValidateAssignments(txt)
    Else
        msg = "Unknown LMS_IMPORT type. Use QUIZ, QUESTION_BANK, or ASSIGNMENTS."
    End If

    MsgBox msg, vbInformation, "LMS import check"
End Sub

Private Function CountQuizQuestions() As Long
    Dim re As Object
    Dim matches As Object
    Set re = CreateObject("VBScript.RegExp")
    re.Global = True
    re.IgnoreCase = True
    re.Pattern = "^Q\d+\."
    Set matches = re.Execute(ActiveDocument.Content.Text)
    CountQuizQuestions = matches.Count
End Function

Private Function ValidateQuiz(ByVal txt As String) As String
    Dim reQ As Object, reA As Object
    Dim qCount As Long, aCount As Long

    Set reQ = CreateObject("VBScript.RegExp")
    reQ.Global = True
    reQ.IgnoreCase = True
    reQ.Pattern = "Q\d+\."
    qCount = reQ.Execute(txt).Count

    Set reA = CreateObject("VBScript.RegExp")
    reA.Global = True
    reA.IgnoreCase = True
    reA.Pattern = "ANSWER:\s*[A-F]"
    aCount = reA.Execute(txt).Count

    If qCount = 0 Then
        ValidateQuiz = "No Q1. / Q2. blocks found."
    ElseIf qCount <> aCount Then
        ValidateQuiz = "Found " & qCount & " question(s) but " & aCount & " ANSWER line(s). Each question needs ANSWER: A–F."
    Else
        ValidateQuiz = "Looks good: " & qCount & " question(s) with matching ANSWER lines." & vbCrLf & _
            "Save as .docx (or PDF) and upload on the quiz edit page."
    End If
End Function

Private Function ValidateAssignments(ByVal txt As String) As String
    Dim re As Object
    Dim titleCount As Long
    Set re = CreateObject("VBScript.RegExp")
    re.Global = True
    re.IgnoreCase = True
    re.Pattern = "TITLE:"
    titleCount = re.Execute(txt).Count

    If titleCount = 0 Then
        ValidateAssignments = "No TITLE: fields found. Insert assignment blocks separated by ---."
    Else
        ValidateAssignments = "Found " & titleCount & " assignment TITLE field(s)." & vbCrLf & _
            "Ensure DELIVERY is file/link/both and DROP_FOLDER_URL is set for link/both."
    End If
End Function
