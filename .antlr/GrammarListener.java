// Generated from c:/Users/angel/Desktop/Universidad/Laboratorio Compi 2/Proyecto 1/OLC2_Proyecto1_202203228/Grammar.g4 by ANTLR 4.13.1
import org.antlr.v4.runtime.tree.ParseTreeListener;

/**
 * This interface defines a complete listener for a parse tree produced by
 * {@link GrammarParser}.
 */
public interface GrammarListener extends ParseTreeListener {
	/**
	 * Enter a parse tree produced by the {@code Program}
	 * labeled alternative in {@link GrammarParser#p}.
	 * @param ctx the parse tree
	 */
	void enterProgram(GrammarParser.ProgramContext ctx);
	/**
	 * Exit a parse tree produced by the {@code Program}
	 * labeled alternative in {@link GrammarParser#p}.
	 * @param ctx the parse tree
	 */
	void exitProgram(GrammarParser.ProgramContext ctx);
	/**
	 * Enter a parse tree produced by the {@code PrintStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void enterPrintStatement(GrammarParser.PrintStatementContext ctx);
	/**
	 * Exit a parse tree produced by the {@code PrintStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void exitPrintStatement(GrammarParser.PrintStatementContext ctx);
	/**
	 * Enter a parse tree produced by the {@code VarDeclaration}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void enterVarDeclaration(GrammarParser.VarDeclarationContext ctx);
	/**
	 * Exit a parse tree produced by the {@code VarDeclaration}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void exitVarDeclaration(GrammarParser.VarDeclarationContext ctx);
	/**
	 * Enter a parse tree produced by the {@code AssignmentStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void enterAssignmentStatement(GrammarParser.AssignmentStatementContext ctx);
	/**
	 * Exit a parse tree produced by the {@code AssignmentStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void exitAssignmentStatement(GrammarParser.AssignmentStatementContext ctx);
	/**
	 * Enter a parse tree produced by the {@code IfStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void enterIfStatement(GrammarParser.IfStatementContext ctx);
	/**
	 * Exit a parse tree produced by the {@code IfStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void exitIfStatement(GrammarParser.IfStatementContext ctx);
	/**
	 * Enter a parse tree produced by the {@code WhileStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void enterWhileStatement(GrammarParser.WhileStatementContext ctx);
	/**
	 * Exit a parse tree produced by the {@code WhileStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void exitWhileStatement(GrammarParser.WhileStatementContext ctx);
	/**
	 * Enter a parse tree produced by the {@code ContinueStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void enterContinueStatement(GrammarParser.ContinueStatementContext ctx);
	/**
	 * Exit a parse tree produced by the {@code ContinueStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void exitContinueStatement(GrammarParser.ContinueStatementContext ctx);
	/**
	 * Enter a parse tree produced by the {@code BreakStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void enterBreakStatement(GrammarParser.BreakStatementContext ctx);
	/**
	 * Exit a parse tree produced by the {@code BreakStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void exitBreakStatement(GrammarParser.BreakStatementContext ctx);
	/**
	 * Enter a parse tree produced by the {@code ReturnStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void enterReturnStatement(GrammarParser.ReturnStatementContext ctx);
	/**
	 * Exit a parse tree produced by the {@code ReturnStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void exitReturnStatement(GrammarParser.ReturnStatementContext ctx);
	/**
	 * Enter a parse tree produced by the {@code FunctionDeclaration}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void enterFunctionDeclaration(GrammarParser.FunctionDeclarationContext ctx);
	/**
	 * Exit a parse tree produced by the {@code FunctionDeclaration}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void exitFunctionDeclaration(GrammarParser.FunctionDeclarationContext ctx);
	/**
	 * Enter a parse tree produced by the {@code FunctionCallStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void enterFunctionCallStatement(GrammarParser.FunctionCallStatementContext ctx);
	/**
	 * Exit a parse tree produced by the {@code FunctionCallStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void exitFunctionCallStatement(GrammarParser.FunctionCallStatementContext ctx);
	/**
	 * Enter a parse tree produced by the {@code ArrayAssignmentStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void enterArrayAssignmentStatement(GrammarParser.ArrayAssignmentStatementContext ctx);
	/**
	 * Exit a parse tree produced by the {@code ArrayAssignmentStatement}
	 * labeled alternative in {@link GrammarParser#stmt}.
	 * @param ctx the parse tree
	 */
	void exitArrayAssignmentStatement(GrammarParser.ArrayAssignmentStatementContext ctx);
	/**
	 * Enter a parse tree produced by the {@code BlockStatement}
	 * labeled alternative in {@link GrammarParser#block}.
	 * @param ctx the parse tree
	 */
	void enterBlockStatement(GrammarParser.BlockStatementContext ctx);
	/**
	 * Exit a parse tree produced by the {@code BlockStatement}
	 * labeled alternative in {@link GrammarParser#block}.
	 * @param ctx the parse tree
	 */
	void exitBlockStatement(GrammarParser.BlockStatementContext ctx);
	/**
	 * Enter a parse tree produced by {@link GrammarParser#else}.
	 * @param ctx the parse tree
	 */
	void enterElse(GrammarParser.ElseContext ctx);
	/**
	 * Exit a parse tree produced by {@link GrammarParser#else}.
	 * @param ctx the parse tree
	 */
	void exitElse(GrammarParser.ElseContext ctx);
	/**
	 * Enter a parse tree produced by {@link GrammarParser#e}.
	 * @param ctx the parse tree
	 */
	void enterE(GrammarParser.EContext ctx);
	/**
	 * Exit a parse tree produced by {@link GrammarParser#e}.
	 * @param ctx the parse tree
	 */
	void exitE(GrammarParser.EContext ctx);
	/**
	 * Enter a parse tree produced by the {@code EqualityExpression}
	 * labeled alternative in {@link GrammarParser#eq}.
	 * @param ctx the parse tree
	 */
	void enterEqualityExpression(GrammarParser.EqualityExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code EqualityExpression}
	 * labeled alternative in {@link GrammarParser#eq}.
	 * @param ctx the parse tree
	 */
	void exitEqualityExpression(GrammarParser.EqualityExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code InequalityExpression}
	 * labeled alternative in {@link GrammarParser#ineq}.
	 * @param ctx the parse tree
	 */
	void enterInequalityExpression(GrammarParser.InequalityExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code InequalityExpression}
	 * labeled alternative in {@link GrammarParser#ineq}.
	 * @param ctx the parse tree
	 */
	void exitInequalityExpression(GrammarParser.InequalityExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code AddExpression}
	 * labeled alternative in {@link GrammarParser#add}.
	 * @param ctx the parse tree
	 */
	void enterAddExpression(GrammarParser.AddExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code AddExpression}
	 * labeled alternative in {@link GrammarParser#add}.
	 * @param ctx the parse tree
	 */
	void exitAddExpression(GrammarParser.AddExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code ProductExpression}
	 * labeled alternative in {@link GrammarParser#prod}.
	 * @param ctx the parse tree
	 */
	void enterProductExpression(GrammarParser.ProductExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code ProductExpression}
	 * labeled alternative in {@link GrammarParser#prod}.
	 * @param ctx the parse tree
	 */
	void exitProductExpression(GrammarParser.ProductExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code PrimaryExpression}
	 * labeled alternative in {@link GrammarParser#unary}.
	 * @param ctx the parse tree
	 */
	void enterPrimaryExpression(GrammarParser.PrimaryExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code PrimaryExpression}
	 * labeled alternative in {@link GrammarParser#unary}.
	 * @param ctx the parse tree
	 */
	void exitPrimaryExpression(GrammarParser.PrimaryExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code UnaryExpression}
	 * labeled alternative in {@link GrammarParser#unary}.
	 * @param ctx the parse tree
	 */
	void enterUnaryExpression(GrammarParser.UnaryExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code UnaryExpression}
	 * labeled alternative in {@link GrammarParser#unary}.
	 * @param ctx the parse tree
	 */
	void exitUnaryExpression(GrammarParser.UnaryExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code GroupedExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void enterGroupedExpression(GrammarParser.GroupedExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code GroupedExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void exitGroupedExpression(GrammarParser.GroupedExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code IntExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void enterIntExpression(GrammarParser.IntExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code IntExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void exitIntExpression(GrammarParser.IntExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code ReferenceExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void enterReferenceExpression(GrammarParser.ReferenceExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code ReferenceExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void exitReferenceExpression(GrammarParser.ReferenceExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code BoolExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void enterBoolExpression(GrammarParser.BoolExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code BoolExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void exitBoolExpression(GrammarParser.BoolExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code FunctionCallExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void enterFunctionCallExpression(GrammarParser.FunctionCallExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code FunctionCallExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void exitFunctionCallExpression(GrammarParser.FunctionCallExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code ArrayExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void enterArrayExpression(GrammarParser.ArrayExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code ArrayExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void exitArrayExpression(GrammarParser.ArrayExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code ArrayAccessExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void enterArrayAccessExpression(GrammarParser.ArrayAccessExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code ArrayAccessExpression}
	 * labeled alternative in {@link GrammarParser#primary}.
	 * @param ctx the parse tree
	 */
	void exitArrayAccessExpression(GrammarParser.ArrayAccessExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code ParameterList}
	 * labeled alternative in {@link GrammarParser#params}.
	 * @param ctx the parse tree
	 */
	void enterParameterList(GrammarParser.ParameterListContext ctx);
	/**
	 * Exit a parse tree produced by the {@code ParameterList}
	 * labeled alternative in {@link GrammarParser#params}.
	 * @param ctx the parse tree
	 */
	void exitParameterList(GrammarParser.ParameterListContext ctx);
	/**
	 * Enter a parse tree produced by the {@code ArgumentList}
	 * labeled alternative in {@link GrammarParser#args}.
	 * @param ctx the parse tree
	 */
	void enterArgumentList(GrammarParser.ArgumentListContext ctx);
	/**
	 * Exit a parse tree produced by the {@code ArgumentList}
	 * labeled alternative in {@link GrammarParser#args}.
	 * @param ctx the parse tree
	 */
	void exitArgumentList(GrammarParser.ArgumentListContext ctx);
}